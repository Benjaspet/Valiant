<?php

declare(strict_types = 1);

namespace Valiant\Libs;

use pocketmine\plugin\PluginBase;

class FormAPI extends PluginBase{

    public function createCustomForm(callable $function = null) : CustomForm {
        return new CustomForm($function);
    }

    public function createSimpleForm(callable $function = null) : SimpleForm {
        return new SimpleForm($function);
    }

    public function createModalForm(callable $function = null) : ModalForm {
        return new ModalForm($function);
    }
}
