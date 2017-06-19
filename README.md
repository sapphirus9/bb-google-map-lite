# BlankBlanc Google Map Lite
BlankBlanc Google Map Lite は WordPress のプラグインです。  
プラグインの導入により、ショートコードで投稿ページや固定ページ内、または任意のテンプレート内に Google マップを配置することができます。

## パラメータの説明
Key | Value
---------- | ----------
key | API キー※
lat | 緯度※
lng | 経度※
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

`※key, lat, lng は必須指定項目`

<br>

## 設置の仕方

#### テンプレート等で呼び出し
~~~~~~~~~~
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
));
~~~~~~~~~~

#### ショートコード
~~~~~~~~~~
[wp_bb_google_map_lite key=APIキー lat=緯度 lng=経度 zoom=16 width=100% height=400px offset_lat=0 offset_lng=0 title=タイトル title_prefix='' title_suffix='' canvas=gml-canvas gmap_link='Google マップを開く' scrollwheel=false disable_double_click_zoom=false zoom_control=true map_type_control=false street_view_control=false rotate_control=false scale_control=false fullscreen_control=false]
~~~~~~~~~~
