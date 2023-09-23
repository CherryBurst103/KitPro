<?php

namespace Terpz710\KitsPro;

use pocketmine\plugin\PluginBase;
use pocketmine\player\Player;
use jojoe77777\FormAPI\CustomForm;
use pocketmine\item\Item;
use pocketmine\item\VanillaItems;
use pocketmine\nbt\tag\StringTag;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\utils\Config;
use pocketmine\item\StringToItemParser;
use pocketmine\item\enchantment\StringToEnchantmentParser;

class Main extends PluginBase {

    public function onEnable(): void {
        $this->saveResource("kits.yml");
        $this->saveResource("messages.yml");
    }

    public function openKitUI(Player $player) {
        $kits = $this->kits->get("kits", []);
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

            $message = $this->messages->get("messages.kit_select.kit_claimed", "You have claimed the %kit_name% kit!");
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

            $message = $this->messages->get("messages.kit_select.kit_claimed", "You have claimed the %kit_name% kit!");
            $message = str_replace("%kit_name%", $kitData["name"], $message);
            $player->sendMessage($message);
        }
    }

    public function closeKitUI(Player $player) {
        $message = $this->messages->get("messages.kit_select.ui_closed", "You have closed the Kit Selection UI.");
        $player->sendMessage($message);
    }

    private function hasPermission(Player $player, $permission) {
        return $player->hasPermission($permission);
    }

    private function parseKitItem($itemData) {
        $itemParser = new StringToItemParser();
        $parsedItem = $itemParser->parse($itemData["item"]);

        if (isset($itemData["name"])) {
            $parsedItem->setCustomName($itemData["name"]);
        }

        if (isset($itemData["enchantments"])) {
            foreach ($itemData["enchantments"] as $enchantment) {
                $enchantmentParser = new StringToEnchantmentParser();
                $enchantmentInstance = $enchantmentParser->parse($enchantment);
                $parsedItem->addEnchantment($enchantmentInstance);
            }
        }

        return $parsedItem;
    }

    public function generateKitPermissions() {
        $kits = $this->kits->get("kits", []);

        foreach ($kits as $kitName => $kitData) {
            $permissionNode = "kitspro.kit." . strtolower($kitName);
            $kitData["permission"] = $permissionNode;
            $kits[$kitName] = $kitData;
        }

        $this->kits->set("kits", $kits);
    }
}
