<?php

namespace Terpz710\KitsPro;

use pocketmine\plugin\PluginBase;
use pocketmine\player\Player;
use jojoe77777\FormAPI\CustomForm;
use pocketmine\item\Item;
use pocketmine\item\ItemFactory;
use pocketmine\nbt\tag\StringTag;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\utils\Config;

class Main extends PluginBase {

    private $kitsConfig;
    private $messagesConfig;

    public function onEnable(): void {
        @mkdir($this->getDataFolder());
        $this->kitsConfig = new Config($this->getDataFolder() . "kits.yml", Config::YAML);
        $this->messagesConfig = new Config($this->getDataFolder() . "messages.yml", Config::YAML);
    }

    public function onDisable(): void {
        $this->kitsConfig->save();
        $this->messagesConfig->save();
    }

    public function openKitUI(Player $player) {
        $kits = $this->kitsConfig->get("kits", []);
        $kitList = [];

        foreach ($kits as $kitName => $kitData) {
            $kitPermission = "kitspro.kit." . strtolower($kitName);
            if ($this->hasPermission($player, $kitPermission)) {
                $kitList[] = $kitData["name"];
            }
        }

        if (empty($kitList)) {
            $player->sendMessage("You don't have permission to access any kits.");
            return;
        }

        $form = new CustomForm(function (Player $player, $data) use ($kitList) {
            if ($data === null) {
                return;
            }

            $kitName = $kitList[$data];
            $this->applyKit($player, $kitName);

            $message = $this->messagesConfig->get("messages.kit_select.kit_claimed", "You have claimed the %kit_name% kit!");
            $message = str_replace("%kit_name%", $kitName, $message);
            $player->sendMessage($message);
        });

        $form->setTitle("Kit Selection");
        $form->addDropdown("Select a Kit", $kitList);

        $player->sendForm($form);
    }

    public function applyKit(Player $player, string $kitName) {
        $kits = $this->kitsConfig->get("kits", []);

        if (isset($kits[$kitName])) {
            $kitData = $kits[$kitName];

            foreach ($kitData["items"] as $itemData) {
                $item = $this->parseKitItem($itemData);
                $player->getInventory()->addItem($item);
            }

            $message = $this->messagesConfig->get("messages.kit_select.kit_claimed", "You have claimed the %kit_name% kit!");
            $message = str_replace("%kit_name%", $kitData["name"], $message);
            $player->sendMessage($message);
        }
    }

    public function closeKitUI(Player $player) {
        $message = $this->messagesConfig->get("messages.kit_select.ui_closed", "You have closed the Kit Selection UI.");
        $player->sendMessage($message);
    }

    private function hasPermission(Player $player, $permission) {
        return $player->hasPermission($permission);
    }

    private function parseKitItem($itemData) {
        $item = ItemFactory::fromString($itemData["item"]);

        if (isset($itemData["name"])) {
            $item->setCustomName($itemData["name"]);
        }

        if (isset($itemData["enchantments"])) {
            foreach ($itemData["enchantments"] as $enchantment) {
                [$enchantmentName, $enchantmentLevel] = explode(":", $enchantment);
                $item->addEnchantment(Enchantment::getEnchantmentByName($enchantmentName)->setLevel($enchantmentLevel));
            }
        }

        return $item;
    }

    public function generateKitPermissions() {
        $kits = $this->kitsConfig->get("kits", []);

        foreach ($kits as $kitName => $kitData) {
            $permissionNode = "kitspro.kit." . strtolower($kitName);
            $kitData["permission"] = $permissionNode;
            $kits[$kitName] = $kitData;
        }

        $this->kitsConfig->set("kits", $kits);
        $this->kitsConfig->save();
    }
}
