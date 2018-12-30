<?php
/**
 * Rendering a text field on edit screen.
 */
class StaticPostTypeFormRendererText
{
    /**
     * Call `build` function and echo its return
     *
     * @param string $field_name
     * @param string $saved_value
     * @param array $options
     * @return void
     */
    public static function render($field_name, $saved_value, $options)
    {
        echo self::build($field_name, $saved_value, $options);
    }

    /**
     * Build a text field
     *
     * @param string $field_name
     * @param string $saved_value
     * @param array $options
     * @return void
     */
    public static function build($field_name, $saved_value, $options)
    {
        $html = StaticPostTypeFormRendererLabel::build($field_name, $options);

        $style_and_class = StaticPostTypeFormRendererHelperStyleAndClass::forInput($options);

        $size = isset($options['size']) ? $options['size'] : '40';
        $placeholder = isset($options['placeholder']) ? $options['placeholder'] : '';
        $html .= '<input name="' . $field_name . '" type="text" value="' . $saved_value . '" size="' . $size . '" placeholder="' . $placeholder . '" ' . $style_and_class . '>';

        $html .= StaticPostTypeFormRendererDescription::build($field_name, $options);

        return $html;
    }
}
