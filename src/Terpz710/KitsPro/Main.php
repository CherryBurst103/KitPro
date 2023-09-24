<?php

namespace Terpz710\KitsPro;

use pocketmine\item\Item;
use pocketmine\plugin\Plugin;
use pocketmine\plugin\PluginBase;
use Terpz710\KitsPro\Command\KitsCommand;
use pocketmine\player\Player;
use pocketmine\utils\Config;
use pocketmine\utils\TextFormat;
use function strval;

class Main extends PluginBase{
    
    public Config $kits;
    
    public ?Plugin $piggyEnchants;

    protected function onEnable(): void
    {
        $server = $this->getServer();
        $server->getPluginManager()->registerEvents(new EventListener($this), $this);
        $server->getCommandMap()->register("kitspro", new CKitsCommand($this));
        $this->saveResource("kits.yml");
        $this->kits = new Config($this->getDataFolder()."kits.yml", Config::YAML);
        $this->piggyEnchants = $this->getServer()->getPluginManager()->getPlugin("PiggyCustomEnchants");
    }

    public function getPrefix(): string{
        return strval($this->getConfig()->get("prefix", "&c[&aChestkits&c] "));
    }

    public function sendKit(Player $player, string $name, string $lore): void{
        $kit = VanillaBlocks::CHEST()->asItem();
        $kit->getNamedTag()
            ->setString("kitspro", $name);
        $kit->setCustomName($name);
        $kit->setLore(array($lore));
        $player->getInventory()->addItem($kit);
    }

    public function isChestKit(Item $item): bool{
        return $item->getNamedTag()->getTag("kitspro") !== null;
    }
}
