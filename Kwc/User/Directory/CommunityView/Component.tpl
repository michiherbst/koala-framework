<div class="<?=$this->cssClass?>">
    <? if (isset($this->searchForm)) echo $this->component($this->searchForm); ?>
    <div class="clear"></div>
    <? if (isset($this->paging)) echo $this->component($this->paging); ?>
    <ul>
    <?=$this->partials($this->data)?>
    </ul>
    <? if (isset($this->paging)) echo $this->component($this->paging); ?>
</div>