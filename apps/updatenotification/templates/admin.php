<?php
declare(strict_types=1);
/**
 * @copyright (c) 2018 Joas Schilling <coding@schilljs.com>
 *
 * @author Joas Schilling <coding@schilljs.com>
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the COPYING file.
 */
script('updatenotification', 'merged');
style('updatenotification', 'admin');
/** @var array $_ */
?>
<?php if($_['withSettings'] === true) { ?>
<div class="section">
	<h2><?php p($l->t('Updates'));?></h2>
	<p><strong><a href="<?php print_unescaped($theme->getBaseUrl()); ?>" rel="noreferrer noopener" target="_blank"><?php p($theme->getTitle()); ?></a> <?php p(OC_Util::getHumanVersion()) ?></strong></p>
</div>
<?php }?>
<div id="updatenotification" data-json="<?php p($_['json']); ?>"></div>
