<?php
declare(strict_types = 1);

namespace kernel\helpers;

use kernel\Application;
use kernel\exception\EmailValidatorException;
use kernel\exception\FileErrorHttpException;
use kernel\exception\ForbiddenHttpException;
use kernel\exception\InvalidDataHttpException;
use kernel\exception\MailValidatorException;
use kernel\exception\NotFoundHttpException;
use kernel\exception\RemoteException;
use kernel\exception\ServerErrorHttpException;
use kernel\helpers\file\File;
use kernel\validators\MailValidator;

/**
 * Класс-помощник для работы с отправкой почтовых сообщений средствами SMTP.
 * Класс реализует шаблон проектирования "Строитель".
 *
 * @package kernel\helpers
 */
class Mail {
	
	/**
	 * @var array|mixed Информация об отправителе
	 */
	private array $sender;
	/**
	 * @var Email[] Массив объектов электронных адресов
	 */
	private array $recipients;
	/**
	 * @var string Грианица сообщения
	 */
	private string $boundary;
	/**
	 * @var string Тема письма
	 */
	private string $subject;
	/**
	 * @var string Сообщение
	 */
	private string $message;
	/**
	 * @var MailValidator Объект валидации
	 */
	private MailValidator $validator;
	/**
	 * @var string Вспомогательные заголовки
	 */
	private string $multipart;
	/**
	 * @var bool Флаг наличия объектов вложения
	 */
	private bool $withFiles = false;
	/**
	 * @var array Конфигурация
	 */
	private array $settings;
	
	/**
	 * Mail constructor.
	 *
	 * @param MailValidator $validator Объект проверки почтовых данных
	 * @param Email[] $recipients Массив объектов электронных адресов
	 * @param array $sender Информация об отправителе ['name', 'email']
	 *
	 * @throws ServerErrorHttpException
	 * @throws EmailValidatorException|MailValidatorException
	 */
	public function __construct(MailValidator $validator, array $recipients, array $sender = []) {
		$this->validator = $validator;
		$this->settings = Application::getInstance()->getConfig()->getActiveSettings('mail');
		
		// Проверка корректности получателей
		foreach ($recipients as $recipient) {
			$recipient->validate();
		}
		
		$this->recipients = $recipients;
		$this->sender = empty($sender) ? $this->settings['smtpFrom'] : $sender;
		$this->validator->validateSender($this->sender);
		$this->boundary = "--" . sha1(uniqid((string)time()));
	}
	
	/**
	 * Устанавливет тему письма
	 *
	 * @param string $subject Тема письма
	 *
	 * @return $this
	 * @throws MailValidatorException
	 */
	public function setSubject(string $subject): Mail {
		$subjectSafe = Data::secureHtmlChars(Data::trim($subject));
		$this->validator->validateSubject($subject);
		$this->subject = $subjectSafe;
		
		return $this;
	}
	
	/**
	 * Устанавливет сообщение письма
	 *
	 * @param string $message Сообщение
	 *
	 * @return $this
	 */
	public function setMessage(string $message): Mail {
		$this->message = Data::secureHtmlChars(Data::trim($message));
		
		return $this;
	}
	
	/**
	 * Добавляет файлы к письму
	 *
	 * @param array $paths Список путей к файлам
	 *
	 * @return $this
	 * @throws ForbiddenHttpException
	 * @throws NotFoundHttpException
	 * @throws ServerErrorHttpException
	 * @throws FileErrorHttpException
	 */
	public function addFiles(array $paths): Mail {
		$this->withFiles = true;
		
		foreach ($paths as $path) {
			if (!file_exists($path)) {
				throw new NotFoundHttpException("Не удалось найти файл {$path}");
			}
			
			if (!is_readable($path)) {
				throw new ForbiddenHttpException("Ошибка чтения файла {$path}");
			}
			
			$file = new File($path);
			$content = $file->getContent();
			$filename = $file->getFileName();
			
			$multipart = "\r\n--{$this->boundary}\r\n";
			$multipart .= "Content-Type: application/octet-stream; name=\"$filename\"\r\n";
			$multipart .= "Content-Transfer-Encoding: base64\r\n";
			$multipart .= "Content-Disposition: attachment; filename=\"$filename\"\r\n";
			$multipart .= "\r\n";
			$multipart .= chunk_split(base64_encode($content));
			
			$this->multipart = $multipart;
		}
		
		return $this;
	}
	
	/**
	 * Производит отправку письма
	 *
	 * @throws InvalidDataHttpException
	 * @throws RemoteException
	 */
	public function send(): void {
		$properties = get_object_vars($this);
		
		foreach ($properties as $propertyName => $propertyValue) {
			if (!in_array($propertyName, ['multipart', 'withFiles']) && empty($propertyValue)) {
				throw new InvalidDataHttpException("Ошибка формирования сообщения. Не возможно сформировать сообщение без {$propertyName}");
			}
		}
		
		$from = !array_key_exists('name', $this->sender) || is_null($this->sender['name']) ? $this->sender['email'] : $this->sender['name'] . "<" . $this->sender['email'] . ">";
		$recipients = (count($this->recipients) > 1) ? join(", ", $this->recipients) : $this->recipients[0];
		// Формирование заголовков сообщения
		$headers[] = "Date: " . date("D, d.M.Y H:i:s") . " UT";
		$headers[] = "Subject: =?" . $this->settings['smtpCharset'] . "?B?" . base64_encode($this->subject) . "=?=";
		$headers[] = "MIME-Version: 1.0";
		$headers[] = "From: " . $from;
		$headers[] = "To: " . $recipients;
		$headers[] = 'X-Mailer: PHP/' . phpversion();
		$headers[] = "Return-Path: " . $this->sender['email'];
		$headers[] = $this->withFiles ? "Content-Type: multipart/mixed; boundary=\"{$this->boundary}" : "Content-type: text/html; charset=" . $this->settings['smtpCharset'];
		$headers = implode("\r\n", $headers) . "\r\n";
		$message = $headers;
		
		if ($this->withFiles) {
			$multipart = "--{$this->boundary}\r\n";
			$multipart .= "Content-Type: text/html; charset=" . $this->settings['smtpCharset'] . "\r\n";
			$multipart .= "Content-Transfer-Encoding: base64\r\n\r\n";
			$multipart .= chunk_split(base64_encode($this->message));
			$multipart .= $this->multipart;
			$multipart .= "\r\n--{$this->boundary}--\r\n";
			
			$message .= $multipart;
		} else {
			$message .= $this->message . "\r\n";
		}
		
		$host = $this->settings['smtpUseSSL'] === true ? "ssl://" . $this->settings['smtpHost'] : $this->settings['smtpHost'];
		$smtpConnection = fsockopen($host, $this->settings['smtpPort'], $errno, $errstr, $this->settings['smtpTimeout']);
		
		// Установка соединения с почтовым сервером
		if (!$smtpConnection) {
			fclose($smtpConnection);
			throw new RemoteException("{$errno}. $errstr");
		}
		
		if (!$this->getServerResponse($smtpConnection, "220")) {
			throw new RemoteException('Ошибка соединения с почтовым сервером');
		}
		
		// Приветствие почтового сервера
		fputs($smtpConnection, "EHLO " . $_SERVER["SERVER_NAME"] . "\r\n");
		
		if (!$this->getServerResponse($smtpConnection, "250")) {
			fputs($smtpConnection, "HELO " . $_SERVER["SERVER_NAME"] . "\r\n");
			if (!$this->getServerResponse($smtpConnection, "250")) {
				fclose($smtpConnection);
				throw new RemoteException("Ошибка приветствия EHLO и HELO");
			}
		}
		
		// Попытка авторизации
		fputs($smtpConnection, "AUTH LOGIN\r\n");
		
		if (!$this->getServerResponse($smtpConnection, "334")) {
			fclose($smtpConnection);
			throw new RemoteException("Не получено разрешение на запрос авторизации");
		}
		
		fputs($smtpConnection, base64_encode($this->settings['smtpUsername']) . "\r\n");
		
		if (!$this->getServerResponse($smtpConnection, "334")) {
			fclose($smtpConnection);
			throw new RemoteException("Ошибка доступа к пользователю. Сервер не принял логин авторизации");
		}
		
		fputs($smtpConnection, base64_encode($this->settings['smtpPassword']) . "\r\n");
		
		if (!$this->getServerResponse($smtpConnection, "235")) {
			fclose($smtpConnection);
			throw new RemoteException("Ошибка проверки пароля. Сервер не принял пароль авторизации");
		}
		
		// Установка отправителя и получателя(ей) письма
		fputs($smtpConnection, "MAIL FROM: {$from}\r\n");
		
		if (!$this->getServerResponse($smtpConnection, "250")) {
			fclose($smtpConnection);
			throw new RemoteException('Ошибка проверки отправителя. Сервер не принял команду MAIL FROM');
		}
		
		$recipients = explode(',', $recipients);
		
		foreach ($recipients as $recipient) {
			fputs($smtpConnection, "RCPT TO: <{$recipient}>" . "\r\n");
			if (!$this->getServerResponse($smtpConnection, "250")) {
				fclose($smtpConnection);
				throw new RemoteException("Ошибка проверки получателей. Сервер не принял команду RCPT TO");
			}
		}
		
		// Проверка данных письма
		fputs($smtpConnection, "DATA\r\n");
		
		if (!$this->getServerResponse($smtpConnection, "354")) {
			fclose($smtpConnection);
			throw new RemoteException("Ошибка проверки данных. Сервер не принял команду DATA");
		}
		
		// Подготова и проверка тела сообщения
		fputs($smtpConnection, $message . "\r\n.\r\n");
		
		if (!$this->getServerResponse($smtpConnection, "250")) {
			fclose($smtpConnection);
			throw new RemoteException("Ошибка отправки сообщения");
		}
		
		// Разрыв аутентификации с почтовым сервером
		fputs($smtpConnection, "QUIT\r\n");
		fclose($smtpConnection);
	}
	
	/**
	 * Получает ответ сервера по работе с сокетами
	 *
	 * @param mixed $socket Сокет
	 * @param string $response Код ответа
	 *
	 * @return bool
	 */
	private function getServerResponse($socket, string $response): bool {
		$serverResponse = null;
		
		do {
			if (!($serverResponse = fgets($socket, 256))) {
				return false;
			}
		} while (substr($serverResponse, 3, 1) != ' ');
		
		return (substr($serverResponse, 0, 3) === $response);
	}
}