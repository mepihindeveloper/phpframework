<?php
declare(strict_types = 1);

namespace kernel\pattern\mvc;

use kernel\Config;
use kernel\exception\InvalidDataHttpException;
use kernel\exception\NotFoundHttpException;
use kernel\exception\ServerErrorHttpException;

/**
 * Class Router
 *
 *
 * Класс маршрутизации устроен по принципу получения <b>$_GET</b> параметров из URL.
 * В базовом представление может иметь поля: <b>module</b>, <b>controller</b>, <b>action</b>, <b>options</b>.
 *
 * Структруа обращения вылядит следующим образом:
 * <i>/index.php?<b>module</b>=module_name&<b>controller</b>=controller_name&
 * <b>action</b>=action_name&<b>options</b></i>.
 *
 * Опции(атрибуты) - это все дополнительные <b>$_GET</b> параметры за исключением базовых.
 *
 * <b>Модуль</b> не имеет преобразований, поскольку является папкой хранения файлов
 *
 * <b>Контроллер</b> преобразуется в <b>N</b>ame<b>C</b>controller:
 * к виду, что каждая первая буква слова заглавная с припиской Controller.
 * Таким образом получается, что файл контроллера будет называться
 * <i>NameController.php</i>, как и его класс.
 *
 * <b>Действие</b> преобразуется в <b>action</b>Name в случае, если оно существует в контроллере.
 *
 * Стоит отметить, что для контроллеров и действий применяется правило преобразования разделений.
 * Если необходимо назвать файл и класс в несколько слов, то применяется логика: <i>Каждая первая буква
 * относительно символа '<b>-</b>' приводится к верхнему регистру</i>.
 *
 * Пример: контроллер <i>user-files</i> => <i>UserFilesController</i>
 *
 * @package kernel\pattern\mvc
 * @property Controller controller
 */
class Router {
	
	/**
	 * @var string|null Наименование модуля по умолчанию
	 */
	private ?string $defaultModule = null;
	/**
	 * @var string Наименование контроллера по умолчанию
	 */
	private string $defaultController = 'main';
	/**
	 * @var string Наименование метода по умолчанию
	 */
	private string $defaultAction = 'index';
	/**
	 * @var string|null Наименование модуля
	 */
	private ?string $moduleName;
	/**
	 * @var string Наименование контроллера
	 */
	private string $controllerName;
	/**
	 * @var string Наименование метода
	 */
	private string $actionName;
	/**
	 * @var array Атрибуты адресной строки
	 */
	private array $urlAttributes;
	/**
	 * @var Controller Объект контроллера
	 */
	private Controller $controllerObject;
	/**
	 * @var Config Объект управления конфигурацией
	 */
	private Config $config;
	
	/**
	 * @throws InvalidDataHttpException
	 * @throws NotFoundHttpException
	 * @throws ServerErrorHttpException
	 */
	function __construct() {
		$this->config = Config::getInstance();
		$hasFriendlyUrl = $this->hasFriendlyUrl();
		$this->urlAttributes = $this->unifyAttributes($hasFriendlyUrl ? $this->getFriendlyUrl() : $_GET);
		
		$this->setModule();
		$this->setController();
		$this->setAction();
	}
	
	/**
	 * Проверяет настройки использования семантического URL (Человекопонятный URL)
	 *
	 * @return bool
	 */
	private function hasFriendlyUrl(): bool {
		return $this->config->has('friendlyUrl');
	}
	
	/**
	 * Унификация атрибутов адресной строки
	 *
	 * @param array $attributes Атрибуты адресной строки
	 *
	 * @return array
	 */
	public function unifyAttributes(array $attributes): array {
		return array_change_key_case(array_map('strtolower', $attributes), CASE_LOWER);
	}
	
	/**
	 * Получает персональные маршруты
	 *
	 * @return array
	 *
	 * @throws InvalidDataHttpException
	 * @throws NotFoundHttpException
	 * @throws ServerErrorHttpException
	 */
	private function getFriendlyUrl(): array {
		$urls = $this->config->getSection('urls');
		
		if (!$urls) {
			throw new InvalidDataHttpException('Не удалось получить конфигурацию маршрутов');
		}
		
		$this->urlAttributes = [];
		
		foreach ($urls as $key => $value) {
			$pattern = is_numeric($key) ? $value : $key;
			
			if (preg_match("#{$pattern}#i", $_SERVER['QUERY_STRING'], $matches)) {
				if (is_array($value)) {
					foreach ($value as $param => $paramValue) {
						$this->urlAttributes[$param] = $paramValue;
					}
				}
				
				$matches = array_filter($matches, function($value, $key) {
					return !is_numeric($key) && !empty($value);
				}, ARRAY_FILTER_USE_BOTH);
				
				$options = explode('&', $_SERVER['QUERY_STRING']);
				array_shift($options);
				
				foreach ($options as $optionParam => $option) {
					$parts = explode('=', $option);
					$options[$parts[0]] = $parts[1];
					unset($options[$optionParam]);
				}
				
				$matches = array_merge($matches, $options);
				
				foreach ($matches as $attributeKey => $attributeValue) {
					$this->urlAttributes[$attributeKey] = $attributeValue;
				}
				
				return $this->urlAttributes;
			}
		}
		
		throw new NotFoundHttpException('Ни один из маршрутов не прошел проверку на соответствие');
	}
	
	/**
	 * Устанавливает наименование модуля
	 *
	 * @return void
	 */
	private function setModule(): void {
		$this->moduleName = $this->hasUrlModule() ? $this->urlAttributes['module'] . '/' : $this->defaultModule;
	}
	
	/**
	 * Проверяет наличия модуля в запросе
	 *
	 * @return bool
	 */
	public function hasUrlModule(): bool {
		return array_key_exists('module', $this->urlAttributes);
	}
	
	/**
	 * Устанавливает наименование контроллера
	 *
	 * @return void
	 */
	private function setController(): void {
		$this->controllerName = ($this->hasUrlController() ?
				$this->normalizeUrlAttribute($this->urlAttributes['controller']) : ucfirst($this->defaultController)) . 'Controller';
	}
	
	/**
	 * Проверяет наличие контроллера в запросе
	 *
	 * @return bool
	 */
	public function hasUrlController(): bool {
		return array_key_exists('controller', $this->urlAttributes);
	}
	
	/**
	 * Нормализирует атрибут адресной строки
	 * Каждая первая буква относительно символа '<b>-</b>' приводится к верхнему регистру.
	 * Пример: index-index-update => IndexIndexUpdate.
	 *
	 * @param string $attribute Атрибут адресной строки
	 *
	 * @return string
	 */
	private function normalizeUrlAttribute(string $attribute): string {
		$value = array_filter(array_map('ucfirst', explode("-", $attribute)));
		
		return implode('', $value);
	}
	
	/**
	 * Устанавливает наименование метода
	 *
	 * @return void
	 */
	private function setAction(): void {
		$this->actionName = 'action' . ($this->hasUrlAction() ?
				$this->normalizeUrlAttribute($this->urlAttributes['action']) : ucfirst($this->defaultAction));
	}
	
	/**
	 * Проверяет наличия метода в запросе
	 *
	 * @return bool
	 */
	public function hasUrlAction() {
		return array_key_exists('action', $this->urlAttributes);
	}
	
	/**
	 * Возвращает объект контроллера
	 *
	 * @return Controller
	 */
	public function getController(): Controller {
		return $this->controllerObject;
	}
	
	/**
	 * @throws NotFoundHttpException
	 */
	public function init() {
		$controllerFile = $this->getControllerFile();
		
		if (!file_exists($controllerFile) || !is_readable($controllerFile)) {
			throw new NotFoundHttpException("Не удалось получить файл контроллера {$controllerFile}");
		}
		
		require_once $controllerFile;
		
		$controllerClass = 'controllers\\' . $this->controllerName;
		$this->controllerObject = new $controllerClass;
		
		if (!method_exists($this->controllerObject, $this->actionName)) {
			throw new NotFoundHttpException("Не удалось получить метод {$this->actionName} у контроллера
            {$controllerClass}");
		}
		
		$this->controllerObject->{$this->actionName}($this->getMethodParams());
	}
	
	/**
	 * Получает путь к файлу контроллера
	 *
	 * @return string
	 */
	private function getControllerFile(): string {
		return (ROOT . 'controllers/' . $this->moduleName . $this->controllerName . '.php');
	}
	
	/**
	 * Получает дополнительные параметры (аргументы метода) из запроса
	 *
	 * @param array $attributes Атрибуты адресной строки
	 *
	 * @return array
	 */
	public function getMethodParams(array $attributes = []) {
		$params = [];
		$urlAttributes = !empty($attributes) ? $attributes : $this->urlAttributes;
		
		foreach ($urlAttributes as $param => $value) {
			if (in_array($param, ['module', 'controller', 'action']))
				continue;
			
			$params[$param] = htmlspecialchars(stripslashes(trim($value)));
		}
		
		return $params;
	}
	
	/**
	 * Получает свойства по умолчанию
	 *
	 * @param string $property Свойство по умолчанию маршрутизации
	 *
	 * @return mixed
	 *
	 * @throws InvalidDataHttpException
	 */
	public function getDefaultProperty(string $property) {
		$defaultProperty = 'default' . ucfirst($property);
		if (!property_exists($this, $defaultProperty)) {
			throw new InvalidDataHttpException("Отсутствует свойство {$property} в классе маршрутизации");
		}
		
		return $this->$defaultProperty;
	}
	
	/**
	 * Возвращает имя контроллера
	 *
	 * @return string
	 */
	public function getControllerName(): string {
		return $this->hasUrlController() ? $this->urlAttributes['controller'] : $this->defaultController;
	}
	
	/**
	 * Возвращает имя действия
	 *
	 * @return string
	 */
	public function getActionName(): string {
		return $this->hasUrlController() ? $this->urlAttributes['action'] : $this->defaultAction;
	}
}