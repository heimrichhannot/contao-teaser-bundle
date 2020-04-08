<?php
/**
 * Contao Open Source CMS
 *
 * Copyright (c) 2020 Heimrich & Hannot GmbH
 *
 * @author  Thomas Körner <t.koerner@heimrich-hannot.de>
 * @license http://www.gnu.org/licences/lgpl-3.0.html LGPL
 */

$image = ['picture' => $this->picture];
if (!$this->linkAll && $this->overwriteMeta && $this->imageUrl) {
    $image['href'] = $this->imageUrl;
}
if ($this->caption) {
    $image['caption'] = $this->caption;
}
$this->insert('image', $image)