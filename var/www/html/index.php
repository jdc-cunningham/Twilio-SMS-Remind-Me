<?php

    if ($_SERVER['REQUEST_METHOD'] == 'POST') {

		require_once(dirname(__FILE__) . DIRECTORY_SEPARATOR.'db-connect.php'); // get db credentials

		// Twilio sms function
		// require db connection
		require_once(dirname(__FILE__) . DIRECTORY_SEPARATOR.'twilio-php/Twilio/autoload.php');
		
		function sendSMS($msg) {
			$sid = ""; // Your Account SID from www.t$
			$token = ""; // Your Auth Token from www.tw$

			$client = new Twilio\Rest\Client($sid, $token);
			$message = $client->messages->create(
				'##########', // Text this number, your number full 10digit phone number no dashes
				array(
					'from' => '##########', // From a valid Twilio number
					'body' => $msg
				)
			);
		}

		$sms_from = $_POST['From'];
		$sms_body = $_POST['Body'];

		if ($sms_from == '+1##########') { // use +1 us version eg. +1##########

			function get_btc_price() {
				$coindesk_dump = file_get_contents("https://api.coindesk.com/v1/bpi/currentprice.json");
				$coindesk_dump = json_decode($coindesk_dump, True);
				$bitcoin_usd = 'The current price of Bitcoin is $' . number_format((float)$coindesk_dump['bpi']['USD']['rate'], 2, '.', '');

				// send SMS by Twilio
				sendSMS($bitcoin_usd);
			}

			function parseMsg($sms) {
				if (strpos($sms, "\n") !== false) {
					$sms_parts = explode("\n", $sms);
					$sms_text = $sms_parts[1];
					// determine minutes or hours
					$remind_time_str = explode(' ', $sms_parts[0])[2];
					
					$return_arr = [];
					$return_arr['message'] = $sms_text;
					
					if (strpos($remind_time_str, 'mins') !== false) {
						// mins
						$return_arr['timestamp'] = (int)(explode('mins', $remind_time_str)[0]) * 60;
						
					}
					else if (strpos($remind_time_str, 'hrs') !== false) {
						// hrs
						$return_arr['timestamp'] = (int)(explode('hrs', $remind_time_str)[0]) * 3600;
					}
					else {
						// invalid
						$return_arr = false;
					}
					
					return $return_arr;
				}
				else {
					return false;
				}
			}

			function save_reminder($sms_body, $dbh) {
				$id = null;
				$sms_comps = parseMsg($sms_body);
				$sms_msg = $sms_comps['message'];
				$remind_at_timestamp = time() + $sms_comps['timestamp'];
				$reminder_sent = 0;

				$stmt = $dbh->prepare('INSERT INTO reminders VALUES (:id, :sms_body, :remind_at_timestamp, :reminder_sent)');
				$stmt->bindParam(':id', $id, PDO::PARAM_INT);
				$stmt->bindParam(':sms_body', $sms_msg, PDO::PARAM_STR);
				$stmt->bindParam(':remind_at_timestamp', $remind_at_timestamp, PDO::PARAM_INT);
				$stmt->bindParam(':reminder_sent', $reminder_sent, PDO::PARAM_INT);
				$stmt->execute();
			}

			// SMS commands
			$cmds = [
				'cmd bitcoin-price' => 'btc price',
				'Remind me' => 'sms reminder'
			];
			
			$cmd = null;
			
			// this loop figures out what command is in your SMS text
			foreach ($cmds as $cmd_key => $text) {
				if (strpos($sms_body, $cmd_key) !== false) {
					$cmd = $text;
				}
			}
			
			// execute the function associated with the SMS command
			switch ($cmd) {
				case 'btc price':
					get_btc_price();
					break;
				case 'sms reminder':
					save_reminder($sms_body, $dbh);
					break;
			}

		}

    }
