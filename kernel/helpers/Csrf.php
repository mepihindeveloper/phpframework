<?php
/*
 * Copyright (c) 2020.
 *
 * Разработчик: Максим Епихин
 * Twitter: https://twitter.com/maximepihin
 */

declare(strict_types = 1);

namespace kernel\helpers;

use kernel\Application;
use kernel\pattern\Singleton;

class Csrf extends Singleton {
	
	private Session $session;
	private string $lifeTime;
	private string $token;
	
	public function __construct() {
		$this->session = Application::getInstance()->getSession();
		$this->lifeTime = Application::getInstance()->getConfig()->getActiveSettings('session')['lifeTime'];
	}
	
	public function set() {
		if ((!$this->session->hasKey('csrf')) || $this->isExpired()) {
			$this->token = $this->generate();
			$this->session->set('csrf', $this->token);
			$this->session->set('csrfStartTime', time());
		}
	}
	
	private function isExpired(): bool {
		return $this->lifeTime === '0' ? false : time() > ($this->session->get('csrfStartTime') + (int)$this->lifeTime);
	}
	
	private function generate() {
		return bin2hex(random_bytes(32));
	}
	
	public function get() {
		return $this->session->get('csrf');
	}
}