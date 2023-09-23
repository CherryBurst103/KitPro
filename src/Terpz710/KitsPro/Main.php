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

    private $kitsConfig;
    private $messagesConfig;

    public function onEnable(): void {
        $this->saveResource("kits.yml");
        $this->saveResource("messages.yml");
        $this->kitsConfig = new Config($this->getDataFolder() . "kits.yml", Config::YAML);
        $this->messagesConfig = new Config($this->getDataFolder() . "messages.yml", Config::YAML);
        $this->getServer()->getCommandMap()->register("kit", new KitCommand($this));
        $this->generateKitPermissions();
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
        $defaultPermission = $kitData["default"];

        if ($this->hasPermission($player, $kitPermission) || ($defaultPermission === "OP" && $player->isOp()) || ($defaultPermission === "TRUE")) {
            $kitList[] = $kitData["name"];
        }
    }

    if (empty($kitList)) {
        $player->sendMessage("You don't have permission to access any kits.");
        return;
    }

    $form = new CustomForm(function (Player $player, $data) use ($kits) {
        if ($data === null) {
            return;
        }

        $kitIndex = $data[0];
        if (isset($kitList[$kitIndex])) {
            $kitName = $kitList[$kitIndex];
            $this->applyKit($player, $kitName);

            $message = $this->messagesConfig->get("messages.kit_select.kit_claimed", "You have claimed the %kit_name% kit!");
            $message = str_replace("%kit_name%", $kitName, $message);
            $player->sendMessage($message);
        }
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

    private function parseKitItem($itemData) {
        $item = Item::get($itemData["id"], $itemData["meta"], $itemData["count"]);
        $item->setCustomName($itemData["name"]);

        foreach ($itemData["enchantments"] as $enchantment) {
            $enchant = Enchantment::getEnchantmentByName($enchantment["name"]);
            if ($enchant !== null) {
                $enchant->setLevel($enchantment["level"]);
                $item->addEnchantment($enchant);
            }
        }

        return $item;
    }

    private function hasPermission(Player $player, $permission) {
        return $player->hasPermission($permission);
    }

    private function generateKitPermissions() {
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
