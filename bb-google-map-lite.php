<?php
/**
 * Plugin Name: BlankBlanc Google Map Lite
 * Plugin URI:
 * Description: ショートコードまたは任意のテンプレート内に Google マップを配置します
 * Author: Naoki Yamamoto
 * Author URI:
 * Version: 0.8.0
 * License: GPLv2 or later
 */

/*
----------------------------------------------------------------------
■パラメータの説明
[key] … API キー※
[lat] … 緯度※
[lng] … 経度※
[zoom] … 初期表示倍率（初期値 16）
[width] … 表示横幅（初期値 100%）
[height] … 表示縦幅（初期値 400px）
[offset_lat] … センターからのズレ（縦方向｜初期値 0）
[offset_lng] … センターからのズレ（横方向｜初期値 0）
[title] … タイトル（初期値 null）
[title_prefix] … タイトルの接頭辞（初期値 null）
[title_suffix] … タイトルの接尾辞（初期値 null）
[canvas] … マップのキャンバス名（初期値 gml-canvas）
[gmap_link] … リンクテキスト（表示させない場合 false｜初期値 Google マップを開く）
[scrollwheel] … マウスホイールによるズームの許可（true/false｜初期値 false）
[disable_double_click_zoom] … ダブルクリックによるズームの許可（true/false｜初期値 false）
[zoom_control] … ズームコントロールの有無（true/false｜初期値 true）
[map_type_control] … マップタイプコントロールの有無（true/false｜初期値 true）
[street_view_control] … ペグマンコントロールの有無（true/false｜初期値 false）
[rotate_control] … 斜め45度画像コントロールの有無（true/false｜初期値 false）
[scale_control] … スケールコントロールの有無（true/false｜初期値 false）
[fullscreen_control] … 全画面モードコントロールの有無（true/false｜初期値 false）
※key, lat, lng は必須指定項目

【設置方法】
■テンプレート等で呼び出し
echo wp_bb_google_map_lite(array(
  'key'                       => 'API キー'
  'lat'                       => '緯度',
  'lng'                       => '経度',
  'zoom'                      => 16,
  'width'                     => '100%',
  'height'                    => '400px',
  'offset_lat'                => 0,
  'offset_lng'                => 0,
  'title'                     => 'タイトル',
  'title_prefix'              => '',
  'title_suffix'              => '',
  'canvas'                    => 'gml-canvas',
  'gmap_link'                 => 'Google マップを開く',
  'scrollwheel'               => false,
  'disable_double_click_zoom' => true,
  'zoom_control'              => true,
  'map_type_control'          => false,
  'street_view_control'       => false,
  'rotate_control'            => false,
  'scale_control'             => false,
  'fullscreen_control'        => false
)); //

■ショートコード
[wp_bb_google_map_lite key=APIキー lat=緯度 lng=経度 zoom=16
 width=100% height=400px offset_lat=0 offset_lng=0 title=タイトル
 title_prefix='' title_suffix='' canvas=gml-canvas gmap_link='Google マップを開く'
 scrollwheel=false disable_double_click_zoom=false zoom_control=true
 map_type_control=false street_view_control=false rotate_control=false
 scale_control=false fullscreen_control=false]
----------------------------------------------------------------------
 */

class BbGoogleMapLite
{
	public $atts = array();
	private $canvas = array();

	/**
	 * 表示
	 */
	public function map($args)
	{
		$args = shortcode_atts(array(
			'key'                       => '',
			'lat'                       => '',
			'lng'                       => '',
			'zoom'                      => 16,
			'width'                     => '100%',
			'height'                    => '400px',
			'offset_lat'                => 0,
			'offset_lng'                => 0,
			'title'                     => '',
			'title_prefix'              => '',
			'title_suffix'              => '',
			'canvas'                    => 'gml-canvas',
			'gmap_link'                 => 'Google マップを開く',
			'scrollwheel'               => false,
			'disable_double_click_zoom' => true,
			'zoom_control'              => true,
			'map_type_control'          => false,
			'street_view_control'       => false,
			'rotate_control'            => false,
			'scale_control'             => false,
			'fullscreen_control'        => false
		), $args);

		// パラメータチェック
		$_err = "<p class=\"bb-gml-error\" style=\"font-size: 12px; line-height: 1.4; color: red;\">[BlankBlanc Google Map Lite]<br>Error: %s</p>\n";
		if (empty($args['key']))
			return sprintf($_err, 'APIキー (key=APIキー) が設定されていません');
		if (empty($args['lat']) || empty($args['lng'])) {
			return sprintf($_err, '緯度・軽度 (lat=座標 lng=座標) が設定されていません');
		}

		$args['_title'] = $args['title_prefix'] . $args['title'] . $args['title_suffix'];
		$args['style'] = " style=\"width:{$args['width']}; height:{$args['height']};\"";
		$args['_lat'] = $args['lat'] + $args['offset_lat'];
		$args['_lng'] = $args['lng'] + $args['offset_lng'];
		if (in_array($args['canvas'], $this->canvas)) {
			$args['canvas'] .= '-' . count($this->canvas);
		}
		$this->canvas[] = $args['canvas'];
		$src = "<div id=\"{$args['canvas']}\"{$args['style']}>{$args['_title']}</div>\n";
		if ($args['gmap_link'] === 'false') $args['gmap_link'] = false;
		if ($args['gmap_link']) {
			$_title = $args['title'] ? '/' . rawurlencode($args['title']) : '';
			$src .= "<div id=\"{$args['canvas']}-link\" class=\"new-window\"><a href=\"https://www.google.com/maps/place{$_title}/@{$args['lat']},{$args['lng']},{$args['zoom']}z\" target=\"_blank\">{$args['gmap_link']}</a></div>\n";
		}
		$this->atts[] = $args;
		return $src;
	}

	/**
	 * JS出力
	 */
	function js()
	{
		if (empty($this->atts)) return null;
		$_script = 'script';
		$src = "<{$_script}>\nfunction initMap() {";
		foreach ($this->atts as $key => $args) {
			$args = array_map(function ($value) {
				return is_bool($value) ? var_export($value, true) : $value;
			}, $args);
			$src .= <<<HTML

	/**
	 * {$args['canvas']}
	 */
	var map{$key} = new google.maps.Map(document.getElementById('{$args['canvas']}'), {
		zoom: {$args['zoom']},
		center: { lat: {$args['_lat']}, lng: {$args['_lng']} },
		scrollwheel: {$args['scrollwheel']},
		disableDoubleClickZoom: {$args['disable_double_click_zoom']},
		zoomControl: {$args['zoom_control']},
		mapTypeControl: {$args['map_type_control']},
		streetViewControl: {$args['street_view_control']},
		rotateControl: {$args['rotate_control']},
		scaleControl: {$args['scale_control']},
		fullscreenControl: {$args['fullscreen_control']}
	});
	var marker = new google.maps.Marker({
		position: { lat: {$args['lat']}, lng: {$args['lng']} },
		title: '{$args['_title']}',
		map: map{$key}
	});

HTML;
		}
		$src .= "}\n</{$_script}>\n";
		$src .= "<{$_script} async defer src=\"https://maps.googleapis.com/maps/api/js?key={$this->atts[0]['key']}&callback=initMap\"></{$_script}>\n";
		return $src;
	}
}


$bb_google_map_lite = new BbGoogleMapLite();
/**
 * 呼び出し用ファンクション登録
 */
function wp_bb_google_map_lite($args = array())
{
	global $bb_google_map_lite;
	return $bb_google_map_lite->map($args);
}
add_shortcode('wp_bb_google_map_lite', 'wp_bb_google_map_lite');


/**
 * JS登録
 */
function add_bb_google_map_lite_script()
{
	global $bb_google_map_lite;
	echo $bb_google_map_lite->js();
}
add_action('wp_footer', 'add_bb_google_map_lite_script', 99);
