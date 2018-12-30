<?php
class StaticPostTypeFormRendererReference {
  public static function render($field_name, $saved_value, $options) {
    echo self::build($field_name, $saved_value, $options);
  }

  public static function build($field_name, $saved_value, $options) {
    $relation_posts = get_posts($options['reference_query_options']);
    $html = '';
    if (empty($relation_posts)) {
      $html .= StaticPostTypeFormRendererLabel::build($field_name, $options);
      $html .= 'nothing to select';
      return $html;
    }

    // select
    $values = array();
    foreach ($relation_posts as $post) {
      array_push($values, array($post->ID => $post->post_title));
    }
    $options['values'] = $values;

    $html = StaticPostTypeFormRendererSelect::build($field_name, $saved_value, $options);

    return $html;
  }
}
