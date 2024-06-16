<?php

namespace JaceApp\Jace\Enums;

class MessageTypeEnum
{
    // TODO: Could change this to actual enums, but it's only available to PHP 8.1. Something to think about.
    const MESSAGE = 'message';
    const INFORMATION = 'information';
    const COMMAND = 'command';
}
