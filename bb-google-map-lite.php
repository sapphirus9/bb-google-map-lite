<?php
/**
 * Plugin Name: BlankBlanc Google Map Lite
 * Plugin URI:
 * Description: ショートコードまたは任意のテンプレート内に Google マップを配置します。（必要な API: Maps JavaScript API, Geocoding API[option]）
 * Author: Naoki Yamamoto
 * Author URI:
 * Version: 1.0.1
 * License: GPLv2 or later
 */

/*
----------------------------------------------------------------------
■パラメータの説明
key … API キー※1
lat … 緯度※2
lng … 経度※2
address … 住所※2
zoom … 初期表示倍率（初期値 16）
width … 表示横幅（初期値 100%）
height … 表示縦幅（初期値 400px）
offset_lat … センターからのズレ（縦方向｜初期値 0）
offset_lng … センターからのズレ（横方向｜初期値 0）
title … タイトル（初期値 null）
title_prefix … タイトルの接頭辞（初期値 null）
title_suffix … タイトルの接尾辞（初期値 null）
canvas … マップのキャンバス名（初期値 gml-canvas）
gmap_link … リンクテキスト（表示させない場合 false｜初期値 Google マップを開く）
scrollwheel … マウスホイールによるズームの許可（true/false｜初期値 false）
disable_double_click_zoom … ダブルクリックによるズームの許可（true/false｜初期値 false）
zoom_control … ズームコントロールの有無（true/false｜初期値 true）
map_type_control … マップタイプコントロールの有無（true/false｜初期値 true）
street_view_control … ペグマンコントロールの有無（true/false｜初期値 false）
rotate_control … 斜め45度画像コントロールの有無（true/false｜初期値 false）
scale_control … スケールコントロールの有無（true/false｜初期値 false）
fullscreen_control … 全画面モードコントロールの有無（true/false｜初期値 false）
※1 key は管理画面の設定またはパラメータの指定が必須
※2 lat & lng（座標）または address は必須項目（座標が優先）

■ショートコード
[wp_bb_google_map_lite key=APIキー lat=緯度 lng=経度 address=住所 zoom=16
 width=100% height=400px offset_lat=0 offset_lng=0 title=タイトル
 title_prefix='' title_suffix='' canvas=gml-canvas gmap_link='Google マップを開く'
 scrollwheel=false disable_double_click_zoom=false zoom_control=true
 map_type_control=false street_view_control=false rotate_control=false
 scale_control=false fullscreen_control=false]

■テンプレート内など
echo wp_bb_google_map_lite(array(
  'key'                       => 'API キー',
  'lat'                       => '緯度',
  'lng'                       => '軽度',
  'address'                   => '住所',
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
  'fullscreen_control'        => false,
));
※ ファンクションでの呼び出しの場合、スタイルシートは追加されません
※ 同一ページ内にショートコードが利用されている場合、ファンクションでの呼び出しは無効になります
----------------------------------------------------------------------
 */

function call_bb_google_map_lite() {
	new BbGoogleMapLite();
}
add_action('plugins_loaded', 'call_bb_google_map_lite');

class BbGoogleMapLite
{
	public $config = array(
		'key'                       => '',
		'lat'                       => '',
		'lng'                       => '',
		'address'                   => '',
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
		'fullscreen_control'        => false,
	);
	public $option;
	private $atts = array();
	private $canvas = array();
	private $version = '1.0.0';
	private $output_err = "<div class=\"not-exist\"><span>地図を取得できませんでした</span></div>";


	public function __construct() {
		$this->config['css'] = plugins_url('css/bb-google-map-lite.css', __FILE__);
		$this->option = get_option('bb_gmap_lite_values');
		add_shortcode('wp_bb_google_map_lite', array($this, 'map'));
		if (!isset($this->option['css']) || !empty($this->option['css'])) {
			add_action('wp_enqueue_scripts', array($this, 'css'), 10);
		}
		add_action('wp_footer', array($this, 'js'), 99);
	}

	/**
	 * 表示
	 */
	public function map($atts) {
		if ($config = $this->option) {
			$config = array_merge($this->config, $config);
		} else {
			$config = $this->config;
		}
		$atts = shortcode_atts($config, $atts);

		// パラメータチェック
		$err = '';
		if (empty($atts['key'])) {
			$err = $this->output_err;
		}
		if (empty($atts['lat']) || empty($atts['lng'])) {
			if (empty($atts['address'])) {
				$err = $this->output_err;
			}
		} else {
			$atts['_lat'] = $atts['lat'] + $atts['offset_lat'];
			$atts['_lng'] = $atts['lng'] + $atts['offset_lng'];
		}
		$atts['_title'] = $atts['title_prefix'] . $atts['title'] . $atts['title_suffix'];
		$_style = array();
		if ($atts['width']) {
			$_style[] = "width: {$atts['width']};";
		}
		if ($atts['height']) {
			$_style[] = "height: {$atts['height']};";
		}
		$style = (!empty($_style)) ? ' style="' . implode(' ', $_style) . '"' : '';
		if (in_array($atts['canvas'], $this->canvas)) {
			$atts['canvas'] .= '-' . count($this->canvas);
		}
		$this->canvas[] = $atts['canvas'];
		$src = "<div class=\"blankblanc-google-map-lite\">\n";
		$src .= "<div id=\"{$atts['canvas']}\" class=\"gml-canvas\"{$style}>{$err}</div>\n";
		if ($atts['gmap_link'] === 'false') {
			$atts['gmap_link'] = false;
		}
		if (!$err && $atts['gmap_link']) {
			$src .= "<div id=\"{$atts['canvas']}-link\" class=\"gml-canvas-link\"></div>\n";
		}
		$this->atts[] = $atts;
		$src .= "</div>\n";
		return $src;
	}

	/**
	 * css出力
	 */
	public function css() {
		global $post;
		if (!is_object($post)) {
			return;
		}
		if (has_shortcode($post->post_content, 'wp_bb_google_map_lite')) {
			if ($this->option['css']) {
				$css = $this->option['css'];
			} else {
				$css = $this->config['css'];
			}
			wp_enqueue_style('bb-google-map-lite', $css, array(), $this->version);
		}
	}

	/**
	 * js出力
	 */
	public function js() {
		$src = '';
		if (empty($this->atts)) {
			return null;
		}
		foreach ($this->atts as $key => $atts) {
			$atts = array_map(function ($value) {
				return is_bool($value) ? var_export($value, true) : $value;
			}, $atts);

			$link = '';
			if (!empty($atts['_lat']) && !empty($atts['_lng'])) { // 座標
				if (!empty($atts['gmap_link'])) {
					$link = <<<EOT

	var link = '<a href="https://www.google.com/maps?q={$atts['lat']},{$atts['lng']}&z={$atts['zoom']}" target="_blank">{$atts['gmap_link']}</a>';
	document.getElementById('{$atts['canvas']}-link').innerHTML = link;
EOT;
				}
				$src .= <<<EOT

	//
	// {$atts['canvas']}
	//
	var map{$key} = new google.maps.Map(document.getElementById('{$atts['canvas']}'), {
		zoom: {$atts['zoom']},
		center: { lat: {$atts['_lat']}, lng: {$atts['_lng']} },
		scrollwheel: {$atts['scrollwheel']},
		disableDoubleClickZoom: {$atts['disable_double_click_zoom']},
		zoomControl: {$atts['zoom_control']},
		mapTypeControl: {$atts['map_type_control']},
		streetViewControl: {$atts['street_view_control']},
		rotateControl: {$atts['rotate_control']},
		scaleControl: {$atts['scale_control']},
		fullscreenControl: {$atts['fullscreen_control']}
	});
	var marker = new google.maps.Marker({
		position: { lat: {$atts['lat']}, lng: {$atts['lng']} },
		title: '{$atts['_title']}',
		map: map{$key}
	});{$link}

EOT;
			} elseif (!empty($atts['address'])) { // 住所
				if (!empty($atts['gmap_link'])) {
					$link = <<<EOT

			var link = '<a href="https://www.google.com/maps?q='+latlng+'&z={$atts['zoom']}" target="_blank">{$atts['gmap_link']}</a>';
			document.getElementById('{$atts['canvas']}-link').innerHTML = link;
EOT;
				}
				$src .= <<<EOT

	//
	// {$atts['canvas']}
	//
	var address = '{$atts['address']}';
	var geocoder = new google.maps.Geocoder();
	geocoder.geocode({'address': address}, function(results, status) {
		if (status === 'OK') {
			var map{$key} = new google.maps.Map(document.getElementById('{$atts['canvas']}'), {
				zoom: {$atts['zoom']},
				center: results[0].geometry.location,
				scrollwheel: {$atts['scrollwheel']},
				disableDoubleClickZoom: {$atts['disable_double_click_zoom']},
				zoomControl: {$atts['zoom_control']},
				mapTypeControl: {$atts['map_type_control']},
				streetViewControl: {$atts['street_view_control']},
				rotateControl: {$atts['rotate_control']},
				scaleControl: {$atts['scale_control']},
				fullscreenControl: {$atts['fullscreen_control']}
			});
			var marker = new google.maps.Marker({
				position: results[0].geometry.location,
				title: '{$atts['_title']}',
				map: map{$key}
			});
			var latlng = results[0].geometry.location;{$link}
		} else {
			document.getElementById('{$atts['canvas']}').innerHTML = '{$this->output_err}';
		}
	});

EOT;
			}
		}
		$script = 'script';
		echo <<<EOT
<{$script}>
/**
 * BlankBlanc Google Map Lite
 */
function initMap() {{$src}}
</{$script}>
<{$script} async defer src="https://maps.googleapis.com/maps/api/js?key={$this->atts[0]['key']}&callback=initMap"></{$script}>

EOT;
	}
}



/**
 * テンプレート用ファンクション登録
 */
function wp_bb_google_map_lite($args = array()) {
	global $post;
	if (isset($post->post_content) && has_shortcode($post->post_content, 'wp_bb_google_map_lite')) {
		return;
	}
	$bb_gmap_lite = new BbGoogleMapLite();
	return $bb_gmap_lite->map($args);
}



/**
 * 管理画面｜設定
 */
function call_bb_gmap_lite_admin() {
	new BbGoogleMapLiteAdmin();
}
add_action('plugins_loaded', 'call_bb_gmap_lite_admin');

class BbGoogleMapLiteAdmin
{
	public function __construct() {
		add_action('admin_menu', array($this, 'add_admin'));
	}

	public function css_admin() {
		$css = plugins_url('css/bb-gmap-lite-admin.css', __FILE__);
		echo "<link rel='stylesheet' href='{$css}' type='text/css' media='all' />\n";
	}

	public function input_admin() {
		if (isset($_POST['bb_gmap_lite_values'])) {
			$bb_gmap_lite_values = array_map(function($val) {
				if ($val === 'true') {
					return true;
				} elseif ($val === 'false') {
					return false;
				} else {
					return $val;
				}
			}, $_POST['bb_gmap_lite_values']);
			check_admin_referer('bb_gmap_lite_nonce');
			if (isset($bb_gmap_lite_values['reset'])) { // 初期値に戻す
				$bb_gmap_lite_values = array();
				delete_option('bb_gmap_lite_values');
			} else {
				update_option('bb_gmap_lite_values', wp_unslash($bb_gmap_lite_values)); // 保存
			}
		} else {
			if (!$bb_gmap_lite_values = get_option('bb_gmap_lite_values')) {
				$bb_gmap_lite_values = array();
			}
		}
		$bb_gmap_lite = new BbGoogleMapLite();
		$bb_gmap_lite_values_default = $bb_gmap_lite->config;
		$bb_gmap_lite_values = array_merge($bb_gmap_lite_values_default, $bb_gmap_lite_values);

		function _echo($val, $prefix = '', $suffix = '') {
			if (is_bool($val)) {
				$val = var_export($val, true);
			} elseif (empty($val)) {
				$val = '指定なし';
			} else {
				$val = $prefix . ' ' . esc_html(str_replace(' ', '&nbsp;', $val)) . ' ' . $suffix;
			}
			echo $val;
		}
	?>
<div class="wrap">
	<h2>Google マップ Lite</h2>
	<p>ショートコード共通の設定を行います。ショートコード内では指定を個別に上書きすることができます。</p>
	<?php if (isset($_POST['bb_gmap_lite_values'])) : ?>
		<div id="setting-error-settings_updated" class="updated settings-error notice is-dismissible">
			<p><strong>設定を保存しました</strong></p>
		</div>
	<?php endif; ?>
	<div id="bb-config-edit">
		<form method="post">
			<fieldset class="apikey">
				<div class="label-title">
					<label for="bb-gmap-lite-apikey">Google マップ API キー</label>
				</div>
				<div class="group">
					<input type="text" name="bb_gmap_lite_values[key]" id="bb-gmap-lite-apikey" class="l-text" value="<?php echo esc_textarea($bb_gmap_lite_values['key']); ?>">
				</div>
				<div class="note">API キーは <a href="https://cloud.google.com/maps-platform/" target="_blank">Google Maps API</a> で設定・取得してください<br>
					※ここで API キーを指定しない場合、使用するショートコード内で指定する必要があります<br>
					※必要な API: Maps JavaScript API, Geocoding API（住所 [address] 指定を行う場合）</div>
			</fieldset>

			<fieldset class="zoom">
				<div class="label-title">
					<label for="bb-gmap-lite-zoom">地図の表示倍率 (zoom)</label>
					<div class="note"><span>初期値</span>zoom=<?php echo esc_html($bb_gmap_lite_values_default['zoom']); ?></div>
				</div>
				<div class="group">
					<input type="number" name="bb_gmap_lite_values[zoom]" id="bb-gmap-lite-zoom" class="num-text" value="<?php echo esc_textarea($bb_gmap_lite_values['zoom']); ?>">
				</div>
			</fieldset>

			<fieldset class="gmap-link">
				<div class="label-title">
					<label for="bb-gmap-lite-gmap-link">Google マップのリンク表示</label>
					<div class="note"><span>初期値</span>gmap_link=<?php echo esc_html($bb_gmap_lite_values_default['gmap_link']); ?></div>
				</div>
				<div class="group">
					<input type="text" name="bb_gmap_lite_values[gmap_link]" id="bb-gmap-lite-gmap-link" class="l-text" value="<?php echo esc_textarea($bb_gmap_lite_values['gmap_link']); ?>">
				</div>
			</fieldset>

			<fieldset class="scrollwheel">
				<div class="label-title">
					<label for="bb-gmap-lite-scrollwheel">マウスホイールによるズーム</label>
					<div class="note"><span>初期値</span>scrollwheel=<?php echo _echo($bb_gmap_lite_values_default['scrollwheel']); ?></div>
				</div>
				<div class="group">
					<input type="hidden" name="bb_gmap_lite_values[scrollwheel]" value="false">
					<input type="checkbox" name="bb_gmap_lite_values[scrollwheel]" id="bb-gmap-lite-scrollwheel" value="true"<?php if ($bb_gmap_lite_values['scrollwheel']) echo ' checked'; ?>>
				</div>
			</fieldset>

			<fieldset class="disable-double-click-zoom">
				<div class="label-title">
					<label for="bb-gmap-lite-disable-double-click-zoom">ダブルクリックによるズーム</label>
					<div class="note"><span>初期値</span>disable_double_click_zoom=<?php echo _echo($bb_gmap_lite_values_default['disable_double_click_zoom']); ?></div>
				</div>
				<div class="group">
					<input type="hidden" name="bb_gmap_lite_values[disable_double_click_zoom]" value="false">
					<input type="checkbox" name="bb_gmap_lite_values[disable_double_click_zoom]" id="bb-gmap-lite-disable-double-click-zoom" value="true"<?php if ($bb_gmap_lite_values['disable_double_click_zoom']) echo ' checked'; ?>>
				</div>
			</fieldset>

			<fieldset class="zoom-control">
				<div class="label-title">
					<label for="bb-gmap-lite-zoom-control">ズームコントロール表示</label>
					<div class="note"><span>初期値</span>zoom_control=<?php echo _echo($bb_gmap_lite_values_default['zoom_control']); ?></div>
				</div>
				<div class="group">
					<input type="hidden" name="bb_gmap_lite_values[zoom_control]" value="false">
					<input type="checkbox" name="bb_gmap_lite_values[zoom_control]" id="bb-gmap-lite-zoom-control" value="true"<?php if ($bb_gmap_lite_values['zoom_control']) echo ' checked'; ?>>
				</div>
			</fieldset>

			<fieldset class="map-type-control">
				<div class="label-title">
					<label for="bb-gmap-lite-map-type-control">マップタイプコントロール表示</label>
					<div class="note"><span>初期値</span>map_type_control=<?php echo _echo($bb_gmap_lite_values_default['map_type_control']); ?></div>
				</div>
				<div class="group">
					<input type="hidden" name="bb_gmap_lite_values[map_type_control]" value="false">
					<input type="checkbox" name="bb_gmap_lite_values[map_type_control]" id="bb-gmap-lite-map-type-control" value="true"<?php if ($bb_gmap_lite_values['map_type_control']) echo ' checked'; ?>>
				</div>
			</fieldset>

			<fieldset class="street-view-control">
				<div class="label-title">
					<label for="bb-gmap-lite-street-view-control">ペグマンコントロール表示</label>
					<div class="note"><span>初期値</span>street_view_control=<?php echo _echo($bb_gmap_lite_values_default['street_view_control']); ?></div>
				</div>
				<div class="group">
					<input type="hidden" name="bb_gmap_lite_values[street_view_control]" value="false">
					<input type="checkbox" name="bb_gmap_lite_values[street_view_control]" id="bb-gmap-lite-street-view-control" value="true"<?php if ($bb_gmap_lite_values['street_view_control']) echo ' checked'; ?>>
				</div>
			</fieldset>

			<fieldset class="rotate-control">
				<div class="label-title">
					<label for="bb-gmap-lite-rotate-control">斜め45度画像コントロール表示</label>
					<div class="note"><span>初期値</span>rotate_control=<?php echo _echo($bb_gmap_lite_values_default['rotate_control']); ?></div>
				</div>
				<div class="group">
					<input type="hidden" name="bb_gmap_lite_values[rotate_control]" value="false">
					<input type="checkbox" name="bb_gmap_lite_values[rotate_control]" id="bb-gmap-lite-rotate-control" value="true"<?php if ($bb_gmap_lite_values['rotate_control']) echo ' checked'; ?>>
				</div>
			</fieldset>

			<fieldset class="scale-control">
				<div class="label-title">
					<label for="bb-gmap-lite-scale-control">スケールコントロール表示</label>
					<div class="note"><span>初期値</span>scale_control=<?php echo _echo($bb_gmap_lite_values_default['scale_control']); ?></div>
				</div>
				<div class="group">
					<input type="hidden" name="bb_gmap_lite_values[scale_control]" value="false">
					<input type="checkbox" name="bb_gmap_lite_values[scale_control]" id="bb-gmap-lite-scale-control" value="true"<?php if ($bb_gmap_lite_values['scale_control']) echo ' checked'; ?>>
				</div>
			</fieldset>

			<fieldset class="fullscreen-control">
				<div class="label-title">
					<label for="bb-gmap-lite-fullscreen-control">全画面モードコントロール表示</label>
					<div class="note"><span>初期値</span>fullscreen_control=<?php echo _echo($bb_gmap_lite_values_default['fullscreen_control']); ?></div>
				</div>
				<div class="group">
					<input type="hidden" name="bb_gmap_lite_values[fullscreen_control]" value="false">
					<input type="checkbox" name="bb_gmap_lite_values[fullscreen_control]" id="bb-gmap-lite-fullscreen-control" value="true"<?php if ($bb_gmap_lite_values['fullscreen_control']) echo ' checked'; ?>>
				</div>
			</fieldset>

			<fieldset class="css-url">
				<div class="label-title">
					<label for="bb-gmap-lite-css-url">スタイルシート URL</label>
				</div>
				<div class="group">
					<input type="text" name="bb_gmap_lite_values[css]" id="bb-gmap-lite-css" class="ll-text" value="<?php echo esc_textarea($bb_gmap_lite_values['css']); ?>">
				</div>
				<div class="note"><span>初期値</span><?php echo esc_html($bb_gmap_lite_values_default['css']); ?><br>
					※空白の場合、スタイルシートの読み込みを行いません</div>
			</fieldset>

			<fieldset class="submit-btn">
				<?php wp_nonce_field('bb_gmap_lite_nonce'); ?>
				<?php submit_button('設定を保存', 'primary', 'bb-gmap-lite-save', false); ?>
				<?php submit_button('初期状態に戻す', 'large', 'bb_gmap_lite_values[reset]', false, array('id' => 'reset-config')); ?>
			</fieldset>
		</form>
		<script>
		(function($) {
			// 設定の初期化
			$(function() {
				$('#reset-config').on('click', function(e) {
					if (!confirm('現在の設定を初期化してよろしいですか？')) e.preventDefault();
				});
			});
		})(jQuery);
		</script>
	</div>

	<dl id="bb-gmap-lite-guide">
		<dt>ショートコード指定の例（lat &amp; lng または address は必須）</dt>
		<dd>[wp_bb_google_map_lite lat=緯度 lng=経度 (or) address=住所]</dd>
		<dt>パラメータの説明</dt>
		<dd>
			<dl>
				<dt>key</dt>
				<dd>API キー ※1</dd>
				<dt>lat</dt>
				<dd>緯度 ※2</dd>
				<dt>lng</dt>
				<dd>経度 ※2</dd>
				<dt>address</dt>
				<dd>住所 ※2</dd>
				<dt>zoom</dt>
				<dd>初期表示倍率（初期値 16）</dd>
				<dt>width</dt>
				<dd>表示横幅（初期値 100%）</dd>
				<dt>height</dt>
				<dd>表示縦幅（初期値 400px）</dd>
				<dt>offset_lat</dt>
				<dd>センターからのズレ（縦方向｜初期値 0）</dd>
				<dt>offset_lng</dt>
				<dd>センターからのズレ（横方向｜初期値 0）</dd>
				<dt>title</dt>
				<dd>タイトル（初期値 null）</dd>
				<dt>title_prefix</dt>
				<dd>タイトルの接頭辞（初期値 null）</dd>
				<dt>title_suffix</dt>
				<dd>タイトルの接尾辞（初期値 null）</dd>
				<dt>canvas</dt>
				<dd>マップのキャンバス名（初期値 gml-canvas）</dd>
				<dt>gmap_link</dt>
				<dd>リンクテキスト（表示させない場合 false｜初期値 Google マップを開く）</dd>
				<dt>scrollwheel</dt>
				<dd>マウスホイールによるズームの許可（true/false｜初期値 false）</dd>
				<dt>disable_double_click_zoom</dt>
				<dd>ダブルクリックによるズームの許可（true/false｜初期値 false）</dd>
				<dt>zoom_control</dt>
				<dd>ズームコントロールの有無（true/false｜初期値 true）</dd>
				<dt>map_type_control</dt>
				<dd>マップタイプコントロールの有無（true/false｜初期値 true）</dd>
				<dt>street_view_control</dt>
				<dd>ペグマンコントロールの有無（true/false｜初期値 false）</dd>
				<dt>rotate_control</dt>
				<dd>斜め45度画像コントロールの有無（true/false｜初期値 false）</dd>
				<dt>scale_control</dt>
				<dd>スケールコントロールの有無（true/false｜初期値 false）</dd>
				<dt>fullscreen_control</dt>
				<dd>全画面モードコントロールの有無（true/false｜初期値 false）</dd>
			</dl>
		</dd>
		<dd class="param-notice">※1 key はこの設定画面またはショートコード内で必須項目<br>
			※2 lat &amp; lng（座標） または address はショートコード内で必須項目（座標が優先）<br>
			（address の利用の場合、要 Geocoding API）</dd>
	</dl>
</div>
	<?php
	}

	public function add_admin() {
		$page = add_options_page('Google マップ Lite', 'Google マップ Lite', 'install_plugins', 'bb_gmap_lite', array($this, 'input_admin'));
		add_action('admin_head-' . $page, array($this, 'css_admin'));
	}
}



/**
 * アンインストール
 */
function bb_gmap_lite_uninstall() {
	delete_option('bb_gmap_lite_values');
}
register_uninstall_hook(__FILE__, 'bb_gmap_lite_uninstall');
