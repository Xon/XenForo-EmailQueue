<?php

class SV_EmailQueue_Option_EmailTemplates
{
    public static function renderOption(XenForo_View $view, $fieldPrefix, array $preparedOption, $canEdit)
    {
        /** @var XenForo_Model_EmailTemplate $emailTemplateModel */
        $emailTemplateModel = XenForo_Model::create('XenForo_Model_EmailTemplate');
        $templates = $emailTemplateModel->getAllEffectiveEmailTemplateTitles();
        $options = array();
        $options[] = array(
                'value' => 0,
                'label' => new XenForo_Phrase('none'),
                'selected' => true,
                'depth' => 0,
            );
        foreach ($templates AS $title => $template)
        {

            if (!empty($preparedOption['option_value']) &&
                is_array($preparedOption['option_value']) &&
                isset($preparedOption['option_value'][$title]))
            {
                continue;
            }

            $options[] = array(
                'value' => $title,
                'label' => $title,
                'selected' => false,
                'depth' => 0,
            );
        }
        $preparedOption['formatParams'] = $options;

        return XenForo_ViewAdmin_Helper_Option::renderOptionTemplateInternal(
            'sv_emailqueue_mailtemplates', $view, $fieldPrefix, $preparedOption, $canEdit,
            array('templates' => $templates)
        );
    }

    public static function verifyOption(array &$values, XenForo_DataWriter $dw, $fieldName)
    {
        /** @var XenForo_Model_EmailTemplate $emailTemplateModel */
        $emailTemplateModel = XenForo_Model::create('XenForo_Model_EmailTemplate');
        $templates = $emailTemplateModel->getAllEffectiveEmailTemplateTitles();

        // pull out new items and re-insert in the correct format, and that it is formated correctly
        foreach($values as $key => $value)
        {
            if (is_numeric($key))
            {
                $values[$value] = true;
                unset($values[$key]);
            }
            else
            {
                $values[$key] = true;
            }
        }

        foreach($values as $key => $value)
        {
            if (!isset($templates[$key]) || empty($value))
            {
                unset($values[$key]);
            }
        }

        ksort($values);

        return true;
    }
}