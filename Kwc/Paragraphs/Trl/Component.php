<?php
class Kwc_Paragraphs_Trl_Component extends Kwc_Chained_Trl_Component
{

    public static function getSettings($masterComponentClass)
    {
        $ret = parent::getSettings($masterComponentClass);
        $ret['componentIcon'] = 'page';
        $ret['generators']['paragraphs']['class'] = 'Kwc_Paragraphs_Trl_Generator';
        $ret['childModel'] = 'Kwc_Paragraphs_Trl_Model';
        $ret['extConfig'] = 'Kwc_Paragraphs_Trl_ExtConfig';
        return $ret;
    }

    public function getTemplateVars(Kwf_Component_Renderer_Abstract $renderer = null)
    {
        $ret = parent::getTemplateVars($renderer);
        $ret['paragraphs'] = array();
        foreach($this->getData()->getChildComponents(array('generator'=>'paragraphs')) as $paragraph) {
            $cssClass = 'kwcParagraphItem';
            $row = $paragraph->chained->row;
            if (Kwc_Abstract::getSetting($this->_getSetting('masterComponentClass'), 'useMobileBreakpoints') && $row->device_visible) {
                $cssClass .= ' ' . $row->device_visible;
            }
            $cssClass .= ' outer'.ucfirst(Kwf_Component_Abstract::formatCssClass($paragraph->chained->componentClass));
            $ret['paragraphs'][] = array(
                'data' => $paragraph,
                'class' => $cssClass
            );
        }
        return $ret;
    }

    public function hasContent()
    {
        $childComponents = $this->getData()->getChildComponents(array('generator' => 'paragraphs'));
        foreach ($childComponents as $c) {
            if ($c->hasContent()) return true;
        }
        return false;
    }
}
