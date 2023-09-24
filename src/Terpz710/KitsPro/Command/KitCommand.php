<?php

declare(strict_types=1);

namespace Terpz710\ProKits\Command;

use Terpz710\KitsPro\Main;
use Terpz710\KitsPro\Form\KitForm;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\plugin\Plugin;
use pocketmine\plugin\PluginOwned;

class CKitsCommand extends Command implements PluginOwned{
    
    private ChestKits $chestkits;

    public function __construct(ChestKits $chestkits) {
        $this->chestkits = $chestkits;
        parent::__construct("kitspro");
        $this->setDescription("Open server kits");
        $this->setPermission("kitspro.kit");
    }

    public function getOwningPlugin(): Plugin
    {
        return $this->chestkits;
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args): void{
        if($sender instanceof Player){
            new CKitsForm(
                $this->chestkits,
                $sender
            );
        } else{
            $this->chestkits->getLogger()->warning("Please use this command in game!");
        }
    }
}
