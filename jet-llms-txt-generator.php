<?php
/**
 * Plugin Name:       Jet LLMs.txt Generator (Minimal)
 * Plugin URI:        https://example.com/jet-llms-txt-generator (仮)
 * Description:       Generates llms.txt related files for LLMs. (Minimal version for rebuilding)
 * Version:           0.0.2  // バージョンを少し上げました
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
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

// =========================================================================
// 管理画面メニューの追加
// =========================================================================

/**
 * 管理画面にプラグイン設定メニューを追加する関数
 */
function jet_llms_txt_admin_menu() {
    add_options_page(
        'LLMs.txt Generator Settings', // ページの<title>タグに出力されるテキスト
        'LLMs.txt Generator',        // メニューに表示されるテキスト
        'manage_options',            // このメニューを操作するために必要な権限
        'jet-llms-txt-settings',     // このメニューページのスラッグ（URLの一部になるユニークな名前）
        'jet_llms_txt_settings_page_html' // メニューページの内容を出力する関数名
    );
}
// 'admin_menu' アクションフックに関数を登録
add_action( 'admin_menu', 'jet_llms_txt_admin_menu' );


// =========================================================================
// 設定ページのHTML出力
// =========================================================================

/**
 * 設定ページのHTMLコンテンツを出力する関数
 * (Settings APIを使うように修正)
 */
function jet_llms_txt_settings_page_html() {
    // 権限チェック
    if ( ! current_user_can( 'manage_options' ) ) {
        return;
    }

    // 設定が保存された時に表示されるメッセージ (オプション)
    if ( isset( $_GET['settings-updated'] ) ) {
        add_settings_error( 'jet_llms_txt_messages', 'jet_llms_txt_message', '設定を保存しました。', 'updated' );
    }
    // エラーメッセージ表示
    settings_errors( 'jet_llms_txt_messages' );
    ?>
    <div class="wrap">
        <h1><?php echo esc_html( get_admin_page_title() ); ?></h1>

        <form action="options.php" method="post">
            <?php
            // Settings API のための隠しフィールド（nonceなど）を出力
            settings_fields( 'jet_llms_txt_options_group' );

            // 登録した設定セクション（と、それに属するフィールド）を出力
            // 'jet-llms-txt-settings' は add_settings_section の第4引数で指定したページスラッグ
            do_settings_sections( 'jet-llms-txt-settings' );

            // 保存ボタンを出力
            submit_button( '設定を保存' );
            ?>
        </form>
    </div>
    <?php
}


// =========================================================================
// Settings API の初期化とコールバック関数
// =========================================================================

/**
 * Settings API を初期化する関数
 */
function jet_llms_txt_settings_init() {
    // 1. 設定オプション名を登録
    register_setting(
        'jet_llms_txt_options_group',     // 設定グループ名
        'jet_llms_txt_options',         // オプション名 (DB保存キー)
        'jet_llms_txt_options_sanitize' // サニタイズコールバック関数
    );

    // --- 一般設定セクション ---
    add_settings_section(
        'jet_llms_txt_section_general',      // セクションID
        '一般設定',                         // セクションタイトル
        'jet_llms_txt_section_general_cb', // セクション説明コールバック
        'jet-llms-txt-settings'              // 表示ページスラッグ
    );

    // 対象投稿タイプフィールド
    add_settings_field(
        'jet_llms_txt_field_post_types', // フィールドID
        '対象投稿タイプ',                 // ラベル
        'jet_llms_txt_field_post_types_cb',// HTML出力コールバック
        'jet-llms-txt-settings',           // 表示ページスラッグ
        'jet_llms_txt_section_general',  // 所属セクションID
        [ 'label_for' => 'jet_llms_post_types' ] // label for 属性
    );

    // --- ルール設定セクション ---
    add_settings_section(
        'jet_llms_txt_section_rules',      // セクションID
        'ルール設定',                     // セクションタイトル
        'jet_llms_txt_section_rules_cb', // セクション説明コールバック
        'jet-llms-txt-settings'          // 表示ページスラッグ
    );

    // ユーザーエージェントルールフィールド
    add_settings_field(
        'jet_llms_txt_field_user_agent', // フィールドID
        'ユーザーエージェントルール<br>(User-agent / Allow / Disallow)', // ラベル
        'jet_llms_txt_field_user_agent_cb', // HTML出力コールバック
        'jet-llms-txt-settings',           // 表示ページスラッグ
        'jet_llms_txt_section_rules',  // 所属セクションID
        [ 'label_for' => 'jet_llms_user_agent_rules' ] // label for 属性
    );

    // カスタムルールフィールド
    add_settings_field(
        'jet_llms_txt_field_custom_rules', // フィールドID
        'カスタムルール<br>(Training / Scraping / APIAccess etc.)', // ラベル
        'jet_llms_txt_field_custom_rules_cb', // HTML出力コールバック
        'jet-llms-txt-settings',           // 表示ページスラッグ
        'jet_llms_txt_section_rules', // 所属セクションID
        [ 'label_for' => 'jet_llms_custom_rules' ] // label for 属性
    );

    // 手動追加URLフィールド
    add_settings_field(
        'jet_llms_txt_field_manual_urls', // フィールドID
        '手動で追加するURL',              // ラベル
        'jet_llms_txt_field_manual_urls_cb', // HTML出力コールバック
        'jet-llms-txt-settings',           // 表示ページスラッグ
        'jet_llms_txt_section_rules', // 所属セクションID
        [ 'label_for' => 'jet_llms_manual_urls' ] // label for 属性
    );

    // --- .md ページ生成設定セクション ---
    add_settings_section(
        'jet_llms_txt_section_md',         // セクションID
        '.md ページ生成設定',              // セクションタイトル
        'jet_llms_txt_section_md_cb',    // セクション説明コールバック
        'jet-llms-txt-settings'           // 表示ページスラッグ
    );

    // .md生成の有効化フィールド
    add_settings_field(
        'jet_llms_txt_field_enable_md',    // フィールドID
        '.md ファイル生成',                 // ラベル
        'jet_llms_txt_field_enable_md_cb', // HTML出力コールバック
        'jet-llms-txt-settings',            // 表示ページスラッグ
        'jet_llms_txt_section_md'       // 所属セクションID
        // label_for はチェックボックス単体なので省略可
    );

    // 含めるメタ情報フィールド
    add_settings_field(
        'jet_llms_txt_field_meta_in_md',  // フィールドID
        '含めるメタ情報',                  // ラベル
        'jet_llms_txt_field_meta_in_md_cb',// HTML出力コールバック
        'jet-llms-txt-settings',            // 表示ページスラッグ
        'jet_llms_txt_section_md',      // 所属セクションID
        [ 'label_for' => 'jet_llms_meta_in_md' ] // fieldset に対応させる
    );

// --- ここに他の設定フィールドも同様に追加 ---

}
// 'admin_init' アクションフックで上記の初期化関数を呼び出す
add_action( 'admin_init', 'jet_llms_txt_settings_init' );


// --- コールバック関数群 ---

/**
 * '一般設定' セクションのコールバック関数
 */
function jet_llms_txt_section_general_cb( $args ) {
    ?>
    <p id="<?php echo esc_attr( $args['id'] ); ?>">llms.txt に含める基本的なコンテンツ設定を行います。</p>
    <?php
}

/**
 * '対象投稿タイプ' フィールドのコールバック関数 (チェックボックス)
 */
function jet_llms_txt_field_post_types_cb( $args ) {
    $options = get_option( 'jet_llms_txt_options' );
    $selected_post_types = isset( $options['post_types'] ) ? (array) $options['post_types'] : [];
    $post_types = get_post_types( [ 'public' => true ], 'objects' );

    $html = '<fieldset id="jet_llms_post_types">';
    foreach ( $post_types as $post_type ) {
        if ( $post_type->name === 'attachment' ) {
            continue;
        }
        $checkbox_id = 'post_type_' . esc_attr( $post_type->name );
        $html .= sprintf(
            '<label for="%s" style="margin-right: 15px;"><input type="checkbox" id="%s" name="jet_llms_txt_options[post_types][]" value="%s" %s> %s (%s)</label><br>',
            $checkbox_id,
            $checkbox_id,
            esc_attr( $post_type->name ),
            checked( in_array( $post_type->name, $selected_post_types, true ), true, false ),
            esc_html( $post_type->label ),
            esc_html( $post_type->name )
        );
    }
    $html .= '</fieldset>';
    $html .= '<p class="description">llms.txt のコンテンツリストに含める投稿タイプを選択してください。</p>';

    echo $html;
}

/**
 * 'ルール設定' セクションのコールバック関数
 */
function jet_llms_txt_section_rules_cb( $args ) {
    ?>
    <p id="<?php echo esc_attr( $args['id'] ); ?>">llms.txt に出力するアクセスルールや追加URLを設定します。</p>
    <?php
}

/**
 * 'ユーザーエージェントルール' フィールドのコールバック関数 (テキストエリア)
 */
function jet_llms_txt_field_user_agent_cb( $args ) {
    $options = get_option( 'jet_llms_txt_options' );
    $value = isset( $options['user_agent_rules'] ) ? $options['user_agent_rules'] : "User-agent: *\nAllow: /"; // デフォルト値の例
    ?>
    <textarea id="jet_llms_user_agent_rules" name="jet_llms_txt_options[user_agent_rules]" rows="5" class="large-text"><?php echo esc_textarea( $value ); ?></textarea>
    <p class="description">例:<br><code>User-agent: *<br>Allow: /wp-content/uploads/<br>Disallow: /private/</code></p>
    <?php
}

/**
 * 'カスタムルール' フィールドのコールバック関数 (テキストエリア)
 */
function jet_llms_txt_field_custom_rules_cb( $args ) {
    $options = get_option( 'jet_llms_txt_options' );
    $value = isset( $options['custom_rules'] ) ? $options['custom_rules'] : '';
    ?>
    <textarea id="jet_llms_custom_rules" name="jet_llms_txt_options[custom_rules]" rows="5" class="large-text"><?php echo esc_textarea( $value ); ?></textarea>
    <p class="description">例:<br><code># Training: allowed<br># Scraping: limited<br># APIAccess: restricted</code><br><code>x-content-license: "(c) <?php echo date('Y'); ?> YourSite. All rights reserved."</code><br><code>x-ai-training-policy: "disallowed"</code> など、自由記述。</p>
    <?php
}

/**
 * '手動追加URL' フィールドのコールバック関数 (テキストエリア)
 */
function jet_llms_txt_field_manual_urls_cb( $args ) {
    $options = get_option( 'jet_llms_txt_options' );
    $value = isset( $options['manual_urls'] ) ? $options['manual_urls'] : '';
    ?>
    <textarea id="jet_llms_manual_urls" name="jet_llms_txt_options[manual_urls]" rows="5" class="large-text"><?php echo esc_textarea( $value ); ?></textarea>
    <p class="description">llms.txt のコンテンツリストに、投稿タイプ以外で手動で追加したいURLがあれば1行に1つずつ入力してください。(例: <code>https://example.com/important-page.html</code>)</p>
    <?php
}


// =========================================================================
// 設定値のサニタイズ（無害化）
// =========================================================================

/**
 * 設定値のサニタイズコールバック関数 (推奨)
 */
function jet_llms_txt_options_sanitize( $input ) {
    $sanitized_input = []; // サニタイズ後の値を格納する配列

    // 'post_types' フィールドの値が存在すればサニタイズ
    if ( isset( $input['post_types'] ) ) {
        $sanitized_input['post_types'] = is_array( $input['post_types'] )
            ? array_map( 'sanitize_key', $input['post_types'] )
            : [];
    } else {
        $sanitized_input['post_types'] = [];
    }

    // user_agent_rules フィールドの値をサニタイズ
    if ( isset( $input['user_agent_rules'] ) ) {
        $sanitized_input['user_agent_rules'] = sanitize_textarea_field( $input['user_agent_rules'] );
    } else {
        $sanitized_input['user_agent_rules'] = '';
    }

    // custom_rules フィールドの値をサニタイズ
    if ( isset( $input['custom_rules'] ) ) {
        $sanitized_input['custom_rules'] = sanitize_textarea_field( $input['custom_rules'] );
    } else {
        $sanitized_input['custom_rules'] = '';
    }

    // manual_urls フィールドの値をサニタイズ
    if ( isset( $input['manual_urls'] ) ) {
        // 改行で区切られたURLリストとして、各行をサニタイズする例 (より厳密には filter_var などでURL形式を検証)
         $lines = explode( "\n", $input['manual_urls'] );
         $sanitized_urls = [];
         foreach( $lines as $line ) {
             $trimmed_line = trim( $line );
             if ( ! empty( $trimmed_line ) ) {
                 // esc_url_raw は DB 保存に適した形式で URL をエスケープ/サニタイズ
                 $sanitized_urls[] = esc_url_raw( $trimmed_line );
             }
         }
         // サニタイズしたURLを改行で結合して保存
         $sanitized_input['manual_urls'] = implode( "\n", $sanitized_urls );
    } else {
        $sanitized_input['manual_urls'] = '';
    }

        // enable_md_generation フィールドの値をサニタイズ
    // チェックされていれば 'yes' を、そうでなければ空文字を保存
    if ( isset( $input['enable_md_generation'] ) && $input['enable_md_generation'] === 'yes' ) {
        $sanitized_input['enable_md_generation'] = 'yes';
    } else {
         $sanitized_input['enable_md_generation'] = ''; // または 'no' でも良い
    }

    // meta_in_md フィールドの値をサニタイズ
    if ( isset( $input['meta_in_md'] ) ) {
        // 配列であることを確認し、各要素を sanitize_key で無害化
        // (許可するキーは 'title', 'publish_date', 'author' など想定)
        $allowed_meta_keys = ['title', 'publish_date', 'author']; // 許可するキーのリストを定義
        $sanitized_meta = [];
        if ( is_array( $input['meta_in_md'] ) ) {
            foreach( $input['meta_in_md'] as $meta_key ) {
                $sanitized_key = sanitize_key( $meta_key );
                if ( in_array( $sanitized_key, $allowed_meta_keys, true ) ) {
                    $sanitized_meta[] = $sanitized_key;
                }
            }
        }
         $sanitized_input['meta_in_md'] = $sanitized_meta;

    } else {
        // チェックが一つも入っていなかった場合、空の配列を保存
        $sanitized_input['meta_in_md'] = [];
    }

// --- ここに他の設定値のサニタイズ処理を追加 ---

// return $sanitized_input; は関数の最後に置く

    // --- ここに他の設定値のサニタイズ処理を追加していきます ---

    return $sanitized_input; // サニタイズ後の配列を返す
}

// --- ここから下に、実際のファイル生成などの機能を追加していきます ---
/**
 * '.md ページ生成設定' セクションのコールバック関数
 */
function jet_llms_txt_section_md_cb( $args ) {
    ?>
    <p id="<?php echo esc_attr( $args['id'] ); ?>">各投稿に対応する .md ファイルの自動生成に関する設定を行います。</p>
    <?php
}

/**
 * '.md生成の有効化' フィールドのコールバック関数 (チェックボックス)
 */
function jet_llms_txt_field_enable_md_cb( $args ) {
    $options = get_option( 'jet_llms_txt_options' );
    // 値が 'yes' であればチェックを入れる
    $checked = isset( $options['enable_md_generation'] ) && $options['enable_md_generation'] === 'yes';
    ?>
    <label>
        <input type="checkbox" name="jet_llms_txt_options[enable_md_generation]" value="yes" <?php checked( $checked, true ); ?> >
        投稿・固定ページの保存時に .md ファイルを自動生成する
    </label>
    <p class="description">有効にすると、対象投稿タイプのコンテンツからMarkdownファイルを生成し、サーバー内に保存します。(保存先は別途定義)</p>
     <?php
}

/**
 * '含めるメタ情報' フィールドのコールバック関数 (チェックボックス群)
 */
function jet_llms_txt_field_meta_in_md_cb( $args ) {
    $options = get_option( 'jet_llms_txt_options' );
    $selected_meta = isset( $options['meta_in_md'] ) ? (array) $options['meta_in_md'] : [];
    // 選択可能なメタ情報のリスト (キー => 表示名)
    $available_meta = [
        'title'        => 'タイトル (Title)',
        'publish_date' => '公開日 (Date)',
        'author'       => '著者 (Author)',
        // 必要に応じて他のメタキーを追加 (例: 'modified_date' => '更新日')
        // カスタムフィールドもここに追加可能だが、取得方法は別途考慮が必要
    ];

    $html = '<fieldset id="jet_llms_meta_in_md">';
    foreach ( $available_meta as $key => $label ) {
         $checkbox_id = 'meta_' . esc_attr( $key );
         $html .= sprintf(
             '<label for="%s" style="margin-right: 15px;"><input type="checkbox" id="%s" name="jet_llms_txt_options[meta_in_md][]" value="%s" %s> %s</label><br>',
             $checkbox_id,
             $checkbox_id,
             esc_attr( $key ), // value にメタ情報のキーを入れる
             checked( in_array( $key, $selected_meta, true ), true, false ), // 保存値にあればチェック
             esc_html( $label ) // 表示名
         );
    }
     $html .= '</fieldset>';
     $html .= '<p class="description">生成される .md ファイルの先頭に含めるメタ情報（YAML Front Matter形式を想定）を選択してください。</p>';

    echo $html;
}

?> 