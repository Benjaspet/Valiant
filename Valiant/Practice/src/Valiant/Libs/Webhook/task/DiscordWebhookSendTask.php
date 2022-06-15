<?php

declare(strict_types = 1);

namespace Valiant\Libs\Webhook\task;

use Valiant\Libs\Webhook\Message;
use Valiant\Libs\Webhook\Webhook;
use pocketmine\scheduler\AsyncTask;
use pocketmine\Server;

class DiscordWebhookSendTask extends AsyncTask {

	protected $webhook;
	protected $message;

	public function __construct(Webhook $webhook, Message $message){
		$this->webhook = $webhook;
		$this->message = $message;
	}

	public function onRun(){
		$ch = curl_init($this->webhook->getURL());
		curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($this->message));
		curl_setopt($ch, CURLOPT_POST,true);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_HTTPHEADER, ["Content-Type: application/json"]);
		$this->setResult(curl_exec($ch));
		curl_close($ch);
	}

	public function onCompletion(Server $server){
		$response = $this->getResult();
		if($response !== ""){
			$server->getLogger()->error("[DiscordWebhookAPI] Got error: " . $response);
		}
	}
}
