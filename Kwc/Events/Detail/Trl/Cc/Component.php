<?php
class Kwc_Events_Detail_Trl_Cc_Component extends Kwc_Directories_Item_Detail_Trl_Cc_Component
{
    public static function modifyItemData(Kwf_Component_Data $new)
    {
        parent::modifyItemData($new);
        $new->start_date = $new->chained->chained->row->start_date;
        $new->end_date = $new->chained->chained->row->end_date;
    }
}
