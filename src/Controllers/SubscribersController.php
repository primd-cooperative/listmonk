<?php

namespace ListMonk\Controllers;

use ListMonk\ListMonk;
use ListMonk\Models\MonkSubscriber;
use GuzzleHttp\Exception\ClientException;
use stdClass;

class SubscribersController
{
	protected $listMonk;

	function __construct(ListMonk $listMonk)
	{
		$this->listMonk = $listMonk;
	}

	function get(int|string $id) {
		if (is_string($id)) {
			$response = $this->listMonk->http(sprintf(
				"/api/subscribers?page=1&per_page=1&query=subscribers.email = '%s'",
				$id
			));

			return new MonkSubscriber(json_decode((string) $response)->data->results[0] ?? NULL);

		} else {
			$response = $this->listMonk->http('/api/subscribers/' . $id);

			return new MonkSubscriber(json_decode((string) $response)->data);

		}
	}

	function getAll(int $page = 1, int $perPage=100) : array
	{
		$response = $this->listMonk->http('/api/subscribers' . '?page=' . $page . '&per_page=' . $perPage);
		$subscribers = json_decode((string) $response)->data->results;
		$subscriberObjects = [];
		foreach ($subscribers as $key => $subscriber) {
			$subscriberObjects[$key] = new MonkSubscriber($subscriber);
		}
		return $subscriberObjects;
	}

	function create(MonkSubscriber $subscriber, $preConfirm = false) {
		$data = [
			'name' => $subscriber->getName(),
			'email' => $subscriber->getEmail(),
			'status' => $subscriber->getStatus(),
			'lists' => $subscriber->getLists(TRUE),
			'preconfirm_subscriptions' => $preConfirm
		];
		if ($subscriber->getAttribs() != null) {
			$data['attribs'] = $subscriber->getAttribs();
		}
		try {
			$response = $this->listMonk->http('/api/subscribers', 'post', $data);
		} catch (ClientException $th) {
			throw new \Exception($th->getResponse()->getBody()->getContents());
		}

		return new MonkSubscriber(json_decode((string) $response)->data);
	}

	function modifyLists($subscriber_ids, $action, $target_list_ids, $status  = null) {
		if (!in_array($action, ["add", "remove", "unsubscribe"])) throw new \Exception("Invalid action, allowed actions are: add, remove, unsubscribe.");
		if ($action == "add") {
			if (!in_array($status, ["confirmed", "unsubscribed", "unconfirmed"])) throw new \Exception("Invalid status, allowed status are: subscribed, unsubscribed, unconfirmed.");
		}
		return $this->listMonk->http('/api/subscribers/lists', 'put', [
			'ids' => $subscriber_ids,
			'action' => $action,
			'target_list_ids' => $target_list_ids,
			'status' => $status
		]);
	}

	function update(MonkSubscriber $subscriber) {
		if ($subscriber->getId() == null) throw new \Exception("Subscriber id is required");
		$response = $this->listMonk->http('/api/subscribers/' . $subscriber->getId(), 'put', $subscriber->toArray());
		return new MonkSubscriber(json_decode((string) $response)->data);
	}

	function delete($id) {
		$stored = $this->get($id);
		if ($stored == null) throw new \Exception("Subscriber not found");
		$response = $this->listMonk->http('/api/subscribers/' . $id, 'delete');
		try {
			$this->get($id);

		} catch (\Throwable) {
			return $stored;
		}
		throw new \Exception("Subscriber not deleted");
	}
}
