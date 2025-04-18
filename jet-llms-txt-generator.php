<?php
/**
 * Plugin Name:       Jet LLMs.txt Generator (Minimal)
 * Plugin URI:        https://example.com/jet-llms-txt-generator (仮)
 * Description:       Generates llms.txt related files for LLMs. (Minimal version for rebuilding)
 * Version:           0.0.1
 * Requires at least: 5.2
 * Requires PHP:      7.4
 * Author:            Your Name or Company (仮)
 * Author URI:        https://example.com/ (仮)
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       jet-llms-txt-generator
 * Domain Path:       /languages
 */

// セキュリティ: PHPファイルへの直接アクセスを禁止します。
// WordPress環境外からのアクセスを防ぐための定型句です。
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * プラグインのメインクラス（または関数の起点）
 *
 * この時点では空ですが、将来的に処理をまとめる場所として用意しておくと構造化しやすいです。
 * もちろん、最初はクラスを使わずに関数を直接定義していく形でも問題ありません。
 */
// class Jet_LLMs_Txt_Generator {
//
//  public function __construct() {
//      // アクションフックやフィルターフックをここに登録していく
//      // add_action( 'plugins_loaded', [ $this, 'load_textdomain' ] );
//  }
//
//  public function load_textdomain() {
//      load_plugin_textdomain( 'jet-llms-txt-generator', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
//  }
//
// }
//
// // プラグインのインスタンスを生成
// new Jet_LLMs_Txt_Generator();


// --- ここから下に必要な処理（関数定義、アクションフック・フィルターフックの登録など）を少しずつ追加していきます ---

// 例: 有効化時に何か処理をする場合 (最初は不要)
// register_activation_hook( __FILE__, 'jet_llms_txt_activate_minimal' );
// function jet_llms_txt_activate_minimal() {
//     // オプションの初期設定など
// }

// 例: 無効化時に何か処理をする場合 (最初は不要)
// register_deactivation_hook( __FILE__, 'jet_llms_txt_deactivate_minimal' );
// function jet_llms_txt_deactivate_minimal() {
//     // スケジュールされたイベントのクリアなど
// }

// 画面上に何か表示するテスト (動作確認用、後で消す)
// add_action('admin_notices', function() {
//     echo '<div class="notice notice-success is-dismissible"><p>Jet LLMs.txt Generator (Minimal) is active!</p></div>';
// });

?>