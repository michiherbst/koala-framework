<div class="<?=$this->cssClass?>" id="<?=$this->data->componentId?>">
    <? if ($this->hasContent) { ?>
    <ul class="links">
        <? foreach ($this->links as $link) { ?>
            <li><?=$this->componentLink($link['component'], $this->data->trlStaticExecute($link['text']))?></li>
        <? } ?>
    </ul>
    <? } ?>
</div>
