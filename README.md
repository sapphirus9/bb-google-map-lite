# BlankBlanc Google Map Lite
BlankBlanc Google Map Lite は WordPress プラグインです。
プラグインの導入により、ショートコードで投稿ページや固定ページ内、または任意のテンプレート内に Google マップを配置することができます。
※API キー は [Google Maps API](https://cloud.google.com/maps-platform/) で設定・取得してください。

## パラメータの説明
Key | Value
---------- | ----------
key | API キー※1
lat | 緯度※2
lng | 経度※2
address | 住所※2
zoom | 初期表示倍率（初期値 16）
width | 表示横幅（初期値 100%）
height | 表示縦幅（初期値 400px）
offset_lat | センターからのズレ（縦方向｜初期値 0）
offset_lng | センターからのズレ（横方向｜初期値 0）
title | タイトル（初期値 null）
title_prefix | タイトルの接頭辞（初期値 null）
title_suffix | タイトルの接尾辞（初期値 null）
canvas | マップのキャンバス名（初期値 gml-canvas）
gmap_link | リンクテキスト（表示させない場合 false｜初期値 Google マップを開く）
scrollwheel | マウスホイールによるズームの許可（true/false｜初期値 false）
disable_double_click_zoom | ダブルクリックによるズームの許可（true/false｜初期値 lse）
zoom_control | ズームコントロールの有無（true/false｜初期値 true）
map_type_control | マップタイプコントロールの有無（true/false｜初期値 true）
street_view_control | ペグマンコントロールの有無（true/false｜初期値 false）
rotate_control | 斜め45度画像コントロールの有無（true/false｜初期値 false）
scale_control | スケールコントロールの有無（true/false｜初期値 false）
fullscreen_control | 全画面モードコントロールの有無（true/false｜初期値 false）

<small>※1 key は管理画面の設定またはパラメータの指定が必須
<br>
※2 lat & lng（座標）または address は必須項目（座標が優先）</small>

<br>

## 設置の仕方

#### テンプレート等で呼び出し
~~~php
echo wp_bb_google_map_lite(array(
	'key'                       => 'API_キー'
	'lat'                       => '緯度',
	'lng'                       => '経度',
	'address'                   => '住所',
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
	'fullscreen_control'        => false,
));
~~~
<small>※ ファンクションでの呼び出しの場合、スタイルシートは追加されません<br>
※ 同一ページ内にショートコードが利用されている場合、ファンクションでの呼び出しは無効になります</small>

###### 最小のパラメータ指定

~~~php
echo wp_bb_google_map_lite(array(
	'lat' => '緯度',
	'lng' => '経度',
));
~~~
~~~php
echo wp_bb_google_map_lite(array(
	'address' => '住所',
));
~~~
<small>※ 上記は API キーが管理画面の設定で指定されていることが前提</small>

<br>

#### ショートコード
~~~
[wp_bb_google_map_lite key=API_キー lat=緯度 lng=経度 address=住所 zoom=16 width=100% height=400px offset_lat=0 offset_lng=0 title=タイトル title_prefix='' title_suffix='' canvas=gml-canvas gmap_link='Google マップを開く' scrollwheel=false disable_double_click_zoom=false zoom_control=true map_type_control=false street_view_control=false rotate_control=false scale_control=false fullscreen_control=false]
~~~

###### 最小のパラメータ指定
~~~
[wp_bb_google_map_lite lat=緯度 lng=経度]
~~~
~~~
[wp_bb_google_map_lite address=住所]
~~~
<small>※ 上記は API キーが管理画面の設定で指定されていることが前提</small>
