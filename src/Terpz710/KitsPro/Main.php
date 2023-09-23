<?php

namespace Terpz710\KitsPro;

use pocketmine\plugin\PluginBase;
use pocketmine\player\Player;
use pocketmine\inventory\ChestInventory;
use pocketmine\item\ItemFactory;
use pocketmine\item\ItemIds;
use pocketmine\nbt\tag\StringTag;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\event\Listener;

class Main extends PluginBase implements Listener {

    private $kitsConfig;
    private $messagesConfig;

    public function onEnable(): void {
        $this->kitsConfig = new Config($this->getDataFolder() . "kits.yml", Config::YAML);
        $this->messagesConfig = new Config($this->getDataFolder() . "messages.yml", Config::YAML);

        $this->getServer()->getPluginManager()->registerEvents($this, $this);
    }

    public function onDisable(): void {
        $this->kitsConfig->save();
        $this->messagesConfig->save();
    }

    public function openKitGUI(Player $player) {
        $kitChest = $this->createKitChest($player);
        $kitChest->setListener([$this, "onKitSelection"]);

        $player->addWindow($kitChest);
    }

    public function createKitChest(Player $player): ChestInventory {
        $kitChest = new ChestInventory();
        $kitChest->setSize(27);
        $kitChest->setName("Kit Selection");

        $kits = $this->kitsConfig->get("kits", []);

        $slot = 0;
        foreach ($kits as $kitName => $kitData) {
            $kitItem = ItemFactory::get(ItemIds::DIAMOND_SWORD);
            $kitItem->setCustomName($kitData["name"]);
            $kitItem->setNamedTag(
                (new CompoundTag())
                    ->setString("kit", $kitName)
            );
            $kitChest->setItem($slot, $kitItem);
            $slot++;
        }

        return $kitChest;
    }

    public function onKitSelection(Player $player, int $slot, Item $item) {
        $nbt = $item->getNamedTag();
        if ($nbt !== null && $nbt->hasTag("kit", StringTag::class)) {
            $kitName = $nbt->getString("kit");
            $this->applyKit($player, $kitName);
            $item = ItemFactory::get(ItemIds::STAINED_CLAY, 14);
            $item->setCustomName("Claimed Kit");
            $player->getInventory()->setItem($slot, $item);
        }
    }

    public function applyKit(Player $player, string $kitName) {
        $kits = $this->kitsConfig->get("kits", []);

        if (isset($kits[$kitName])) {
            $kitData = $kits[$kitName];

            foreach ($kitData["items"] as $itemData) {
                $item = ItemFactory::fromString($itemData);
                $player->getInventory()->addItem($item);
            }

            $message = $this->messagesConfig->get("messages.kit_select.kit_claimed", "You have claimed the %kit_name% kit!");
            $message = str_replace("%kit_name%", $kitData["name"], $message);
            $player->sendMessage($message);
        } else {
            $message = $this->messagesConfig->get("messages.kit_select.kit_unknown", "Unknown kit: %kit_name%");
            $message = str_replace("%kit_name%", $kitName, $message);
            $player->sendMessage($message);
        }
    }
}
