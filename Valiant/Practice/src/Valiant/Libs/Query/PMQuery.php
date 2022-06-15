<?php

namespace Valiant\Libs\Query;

use Valiant\Libs\Query\PmQueryException;

class PMQuery {

	public static function query(string $host, int $port, int $timeout = 0): array {
		$socket = @fsockopen('udp://'.$host, $port, $errno, $errstr, $timeout);

		if($errno and $socket !== false) {
			fclose($socket);
			throw new PmQueryException($errstr, $errno);
		}elseif($socket === false) {
			throw new PmQueryException($errstr, $errno);
		}

		stream_Set_Timeout($socket, $timeout);
		stream_Set_Blocking($socket, true);

		$OFFLINE_MESSAGE_DATA_ID = \pack('c*', 0x00, 0xFF, 0xFF, 0x00, 0xFE, 0xFE, 0xFE, 0xFE, 0xFD, 0xFD, 0xFD, 0xFD, 0x12, 0x34, 0x56, 0x78);
		$command = \pack('cQ', 0x01, time());
		$command .= $OFFLINE_MESSAGE_DATA_ID;
		$command .= \pack('Q', 2);
		$length = \strlen($command);

		if($length !== fwrite($socket, $command, $length)) {
			throw new PmQueryException("Failed to write on socket.", E_WARNING);
		}

		$data = fread($socket, 4096);
		fclose($socket);

		if(empty($data) or $data === false) {
			throw new PmQueryException("Server failed to respond", E_WARNING);
		}
		if(substr($data, 0, 1) !== "\x1C") {
			throw new PmQueryException("First byte is not ID_UNCONNECTED_PONG.", E_WARNING);
		}
		if(substr($data, 17, 16) !== $OFFLINE_MESSAGE_DATA_ID) {
			throw new PmQueryException("Magic bytes do not match.");
		}

		$data = \substr($data, 35);
		$data = \explode(';', $data);

		return [
			'GameName' => $data[0] ?? null,
			'HostName' => $data[1] ?? null,
			'Protocol' => $data[2] ?? null,
			'Version' => $data[3] ?? null,
			'Players' => $data[4] ?? null,
			'MaxPlayers' => $data[5] ?? null,
			'ServerId' => $data[6] ?? null,
			'Map' => $data[7] ?? null,
			'GameMode' => $data[8] ?? null,
			'NintendoLimited' => $data[9] ?? null,
			'IPv4Port' => $data[10] ?? null,
			'IPv6Port' => $data[11] ?? null,
			'Extra' => $data[12] ?? null, // TODO: What's in this?
		];
	}
}
