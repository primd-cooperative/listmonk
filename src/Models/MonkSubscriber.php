<?php

namespace AdnanHussainTurki\ListMonk\Models;

class MonkSubscriber {

    private $id;
    private $created_at;
    private $updated_at;
    private $uuid;
    private $email;
    private $name;
    private $attribs = [];
    private $status;
    private $lists = [];

    private $allowed_status = ["enabled", "disabled", "blocklisted"];

    // Constructor to initialize the object
    public function __construct(?object $subscriber = null) {
        if ($subscriber == null) return;
        $this->id = $subscriber->id;
        $this->created_at = $subscriber->created_at;
        $this->updated_at = $subscriber->updated_at;
        $this->uuid = $subscriber->uuid;
        $this->email = $subscriber->email;
        $this->name = $subscriber->name;
        $this->attribs = $subscriber->attribs;
        $this->status = $subscriber->status;
        $this->lists = array_map(
            fn($list) => new MonkList($list),
            $subscriber->lists
        );
    }

    public function getId() {
        return $this->id;
    }

    public function setId($id) {
        $this->id = $id;
    }

    public function getCreatedAt() {
        return $this->created_at;
    }

    public function setCreatedAt($createdAt) {
        $this->created_at = $createdAt;
    }

    public function getUpdatedAt() {
        return $this->updated_at;
    }

    public function setUpdatedAt($updatedAt) {
        $this->updated_at = $updatedAt;
    }

    public function getUuid() {
        return $this->uuid;
    }

    public function setUuid($uuid) {
        $this->uuid = $uuid;
    }

    public function getEmail() {
        return $this->email;
    }

    public function setEmail($email) {
        $this->email= $email;
    }

    public function getName() {
        return $this->name;
    }

    public function setName($name) {
        $this->name= $name;
    }

    public function getAttribs() {
        return $this->attribs;
    }

    public function setAttribs($attribs) {
        $this->attribs= $attribs;
    }

    public function getStatus() {
        return $this->status;
    }

    public function setStatus($status) {
        if (!in_array($status, $this->allowed_status)) {
            throw new \Exception("Invalid status, allowed status are: " . implode(", ", $this->allowed_status) . ".");
        }
        $this->status= $status;
    }

    public function getLists($ids_only = FALSE) {
        if ($ids_only) {
            return array_map(fn($list) => (int) $list->getId(), $this->lists);
        }

        return $this->lists;
    }

    public function hasList(MonkList $list) {
        foreach ($this->lists as $sub_list) {
            if ($list->getId() == $sub_list->getId()) {
                return TRUE;
            }
        }

        return FALSE;
    }

    public function removeList(MonkList|int $list) {
        if ($list instanceof MonkList) {
            $id = $list->getId();
        } else {
            $id = $list;
        }

        foreach ($this->lists as $i => $sub_list) {
            if ($sub_list->getId() == $id) {
                unset($this->lists[$i]);
            }
        }

        $this->lists = array_values($this->lists);
    }

    public function removeLists(MonkList|int ...$lists) {
        foreach ($lists as $list) {
            $this->removeList($list);
        }
    }

    public function setLists($lists) {
        $this->lists= $lists;
    }

    public function toArray() {
        return [
            'id' => $this->id,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'uuid' => $this->uuid,
            'email' => $this->email,
            'name' => $this->name,
            'attribs' => $this->attribs,
            'status' => $this->status,
            'lists' => $this->lists
        ];
    }

    public function toJson() {
        return json_encode($this->toArray());
    }
}
