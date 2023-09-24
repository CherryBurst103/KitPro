<?php

declare(strict_types=1);

namespace Terpz710\ProKits\Form;

use Terpz710\ProKits\Main;
use Vecnavium\FormsUI\SimpleForm;
use pocketmine\player\Player;

class KitForm {
    
    private Player $player;
    
    private ChestKits $chestkits;

    public function __construct(ChestKits $chestkits, Player $player){
        $this->chestkits = $chestkits;
        $this->player = $player;
        $this->openForm($this->player);
    }

    private function openForm(Player $player): void {
        $form = new SimpleForm(function (Player $player, $data){
            if(!isset($data)){
                return false;
            }
            $this->PurchaseForm(
                $player,
                $data
            );
        });
        if(empty($this->chestkits->kits->getAll())){
            return;
        }
        foreach ($this->chestkits->kits->getAll() as $key){
            $form->addButton($key["name"]);
        }
        $config = $this->chestkits->getConfig();
        $form->setTitle($config->get("form.title", "Chestkits Form"));
        $form->setContent($config->get("form.content", "Choose kit you want to buy:"));
        $player->sendForm($form);
    }

    private function PurchaseForm(Player $player, int $key): void
    {
        $plugin = $this->chestkits;
        $kits = $plugin->kits->get(array_keys($plugin->kits->getAll())[$key]);
        $form = new SimpleForm(function (Player $player, $data) use ($kits, $plugin){
            if(!isset($data)){
                $this->openForm($player);
                return;
            }
            switch ($data){
                case 0:
                    $chestkits = $this->chestkits;
                    $chestkits->sendKit(
                        $player,
                        $kits["name"],
                        $kits["lore"]
                    );
                    $player->sendMessage($plugin->getMessage("purchase.success"));
                case 1:
                    //NOOP
                    break;
            }
        });
        $config = $this->chestkits->getConfig();
        $form->setTitle($config->get("purchase.title", "Purchase Form"));
        $form->setContent($kits["content"]);
        $form->addButton($config->get("purchase.accept", "Accept"));
        $form->addButton($config->get("purchase.decline", "Decline"));
        $player->sendForm($form);
    }
}
