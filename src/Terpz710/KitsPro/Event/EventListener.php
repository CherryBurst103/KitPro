<?php

namespace Terpz710\ProKits\Event;

use pocketmine\event\Listener;
use pocketmine\event\block\BlockPlaceEvent;
use pocketmine\item\Item;
use pocketmine\item\enchantment\EnchantmentInstance;
use pocketmine\item\enchantment\StringToEnchantmentParser;
use pocketmine\item\StringToItemParser,
use pocketmine\item\VanillaItems;
use DaPigGuy\PiggyCustomEnchants\enchants\CustomEnchantManager;

class EventListener implements Listener{
    
    private ChestKits $chestkits;

    public function __construct(ChestKits $chestkits)
    {
        $this->chestkits = $chestkits;
    }

    public function onTap(BlockPlaceEvent $event): void{
        $item = $event->getItem();
        $player = $event->getPlayer();
        if($this->chestkits->isChestKit($item)){
            $kitname = $item->getNamedTag()->getString("prokits");
            foreach ($this->chestkits->kits->getAll() as $kits){
                if($kits["name"] == $kitname){
                    foreach($kits["items"] as $itemString){
                        $player->getInventory()->addItem($this->loadItem(...explode(":", $itemString)));
                    }
                    isset($kits["helmet"]) and $player->getInventory()->addItem($this->loadItem(...explode(":", $kits["helmet"])));
                    isset($kits["chestplate"]) and $player->getInventory()->addItem($this->loadItem(...explode(":", $kits["chestplate"])));
                    isset($kits["leggings"]) and $player->getInventory()->addItem($this->loadItem(...explode(":", $kits["leggings"])));
                    isset($kits["boots"]) and $player->getInventory()->addItem($this->loadItem(...explode(":", $kits["boots"])));
                }
            }
            $item->setCount($item->getCount() - 1);
            $player->getInventory()->setItemInHand($item);
            $event->cancel();
        }
    }

    public function loadItem(string $itemName, int $amount, string $name = "default", ...$enchantments): Item{
        $item = StringToItemParser::getInstance()->parse($itemName);
        if ($item === null) {
            $item = VanillaItems::AIR();
        }
        $item->setCount($amount);
        if(strtolower($name) !== "default"){
            $item->setCustomName($name);
        }
        $enchantment = null;
        foreach($enchantments as $key => $name_level){
            if($key % 2 === 0){
                $enchantment = StringToEnchantmentParser::getInstance()->parse((string)$name_level);
                if($enchantment === null && class_exists(CustomEnchantManager::class)){
                    $enchantment = CustomEnchantManager::getEnchantmentByName((string)$name_level);
                }
            }elseif($enchantment !== null){
                $item->addEnchantment(new EnchantmentInstance($enchantment, (int)$name_level));
            }
        }

        return $item;
    }
}