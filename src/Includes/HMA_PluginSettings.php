<?php

namespace HearMe\Includes;

use HearMe\Exceptions\HMA_NotFoundEpisodeException;
use HearMe\Exceptions\HMA_InternalServerErrorException;

final class HMA_PluginSettings
{
  /**
   * @var string[]
   */
  private $options;

  /**
   * @var bool
   */

  /**
   * @var string[]
   */
  private $errors;

  /**
   * @var HMA_Twig
   */
  private $twig;

  /**
   * @var HMA_Connector
   */
  private $connector;

  public function __construct(HMA_Twig $twig, HMA_Connector $connector)
  {
    $this->twig = $twig;
    $this->connector = $connector;
  }

  public function print_section_info()
  {
    print HMA_Translations::get('enter_settings');
  }

  public function textInput(array $args)
  {
    $this->twig->render('includes/partial/textInput', [
      'field' => $args['field'] ?: null,
      'title' => $args['title'] ?: null,
      'description' => $args['description'] ?: null,
      'value' => $args['field'] ? ($this->options[$args['field']] ?? null) : null,
      'error' => $args['field'] ? ($this->errors[$args['field']] ?? null) : null
    ]);
  }


  public function numberInput(array $args)
  {
    $this->twig->render('includes/partial/numberInput', [
      'title' => $args['title'] ?: null,
      'field' => $args['field'] ?: null,
      'description' => $args['description'] ?: null,
      'value' => $args['field'] ? ($this->options[$args['field']] ?? null) : null,
      'error' => $args['field'] ? ($this->errors[$args['field']] ?? null) : null
    ]);
  }

  public function draftInput(array $args)
  {
    $this->twig->render('includes/partial/draftInput', [
      'title' => $args['title'] ?: null,
      'field' => $args['field'] ?: null,
      'description' => $args['description'] ?: null,
      'value' => $args['field'] ? ($this->options[$args['field']] ?? 5) : null,
      'error' => $args['field'] ? ($this->errors[$args['field']] ?? null) : null
    ]);
  }


  public function buttonInput(array $args)
  {
    $this->twig->render('includes/partial/buttonInput', [
      'title' => $args['title'] ?: null,
      'field' => $args['field'] ?: null,
      'description' => $args['description'] ?: null,
      'value' => $args['field'] ? ($this->options[$args['field']] ?? null) : null,
      'error' => $args['field'] ? ($this->errors[$args['field']] ?? null) : null,
      'isApiKey' => isset($this->options['api_key']) && $this->options['api_key'],
      'postLimit' => $this->options['draft_number'] ?? 5,
    ]);
  }

  public function selectInput(array $args)
  {
    $this->twig->render('includes/partial/selectInput', [
      'field' => $args['field'] ?: null,
      'title' => $args['title'] ?: null,
      'description' => $args['description'] ?: null,
      'value' => $args['field'] ? ($this->options[$args['field']] ?? 'before_p') : null,
      'error' => $args['field'] ? ($this->errors[$args['field']] ?? null) : null,
      'options' => $args['options'] ?? [],
      'assetsPath' => $args['assetsPath'],
    ]);
  }

  public function checkboxInput(array $args)
  {
    $this->twig->render('includes/partial/checkboxInput', [
      'field' => $args['field'] ?: null,
      'title' => $args['title'] ?: null,
      'description' => $args['description'] ?: null,
      'error' => $args['field'] ? ($this->errors[$args['field']] ?? null) : null,
      'post_types' => $args['post_types'],
      'options' => $args['field'] ? ($this->options[$args['field']] ?? null) : null,
    ]);
  }
  public function checkboxSingleInput(array $args)
  {
    $this->twig->render('includes/partial/checkboxSingleInput', [
      'title' => $args['title'] ?: null,
      'field' => $args['field'] ?: null,
      'description' => $args['description'] ?: null,
      'value' => $args['field'] ? ($this->options[$args['field']] ?? null) : null,
      'error' => $args['field'] ? ($this->errors[$args['field']] ?? null) : null,
    ]);
  }
  public function initialize()
  {
    register_setting(
      'hear_me_option_group',
      'hear_me_options',
      [$this, 'sanitize']
    );

    add_settings_section(
      'setting_section_id',
      "",
      null,
      'hear-me-settings'
    );

    add_settings_field(
      'api_key',
      HMA_Translations::get('api_key'),
      [$this, 'textInput'],
      'hear-me-settings',
      'setting_section_id',
      ['class' => 'api_tr', 'field' => 'api_key', 'title' => HMA_Translations::get('api_key'), 'description' => HMA_Translations::get('api_key_description')]
    );

    add_settings_field(
      'insert_type',
      HMA_Translations::get('insert_type'),
      [$this, 'selectInput'],
      'hear-me-settings',
      'setting_section_id',
      [
        'field' => 'insert_type',
        'title' => HMA_Translations::get('insert_type'),
        'options' => [
          HMA_Translations::get('before_p') => 'before_p',
          HMA_Translations::get('after_p') => 'after_p',
          HMA_Translations::get('before_h') => 'before_h',
          HMA_Translations::get('after_h') => 'after_h',
        ],
        'description' => HMA_Translations::get('insert_type_description'),
        'assetsPath' => HEAR_ME_ABS_URL . '/assets/img/'
      ]
    );

    add_settings_field(
      'insert_number',
      '{n}-th paragraph',
      [$this, 'numberInput'],
      'hear-me-settings',
      'setting_section_id',
      [
        'field' => 'insert_number', 
        'title' => '{N}-Th Paragraph',
        'description' => HMA_Translations::get('insert_number_description')
      ]
    );
    add_settings_field(
      'hide_player',
      'Hide player',
      [$this, 'checkboxSingleInput'],
      'hear-me-settings',
      'setting_section_id',
      [
        'field' => 'hide_player', 
        'title' => 'Don`t show player',
        'description' => HMA_Translations::get('hide_player')
      ]
    );

    add_settings_field(
      'draft_number',
      'How many post send to HearMe.ai',
      [$this, 'draftInput'],
      'hear-me-settings',
      'setting_section_id',
      [
        'field' => 'draft_number', 
        'title' => 'How Many Post Send To Hearme.Ai',
        'description' => 'Please select how many last post you want to send to HearMe.ai, set -1 for all posts.'
      ]
    );

    add_settings_field(
      'generate_drafts',
      'Send X posts to HearMe.ai',
      [$this, 'buttonInput'],
      'hear-me-settings',
      'setting_section_id',
      [
        'field' => 'generate_drafts',
        'title' => 'Send X Posts To Hearme.Ai',
        'description' => HMA_Translations::get('generate_drafts_description'),
      ]
    );

    add_settings_field(
      'supported_posts',
      'Supported Custom Posts',
      [$this, 'checkboxInput'],
      'hear-me-settings',
      'setting_section_id',
      [
        'field' => 'supported_posts',
        'title' => 'Supported Custom Posts',
        'description' => 'Please check all custom post types that should be supported by HearMe.ai',
        'post_types' => get_post_types(array('public' => true, '_builtin' => false), 'objects'),
      ]
    );
  }

  public function sanitize($input)
  {
    $new_input = array();
    if (isset($input['api_key']))
      $new_input['api_key'] = trim($input['api_key']);

    if (isset($input['insert_number']))
      $new_input['insert_number'] = trim($input['insert_number']);

    if (isset($input['insert_type']))
      $new_input['insert_type'] = trim($input['insert_type']);

    if (isset($input['draft_number']))
      $new_input['draft_number'] = trim($input['draft_number']);

    if (isset($input['hide_player']))
      $new_input['hide_player'] = trim($input['hide_player'][0]);


    if (isset($input['supported_posts']))
      $new_input['supported_posts'] = (array)($input['supported_posts']);

    return $new_input;
  }

  public function menu()
  {
    add_menu_page(
      'HearMe.ai',
      HMA_Translations::get('hear_me_settings_title'),
      'manage_options',
      'hear-me-settings',
      [$this, 'renderSettingsPage'],
      HEAR_ME_ABS_URL . '/assets/img/logo-nav.svg',
    );
  }

  public function renderSettingsPage()
  {
    if ($_POST) {
      $post_options = $this->sanitize($_POST['hear_me_options']);
      update_option('hear_me_options', $post_options);
      echo ('<script type="text/JavaScript"> location.reload(); </script>');
    } else {
      $this->options = get_option('hear_me_options');
      try {
        $this->connector->checkKey();
      } catch (HMA_NotFoundEpisodeException $exception) {
        $this->errors['api_key'] = HMA_Translations::get('api_key_error');
        $this->options['api_key'] = null;
        update_option('hear_me_options', $this->options);
      } catch (HMA_InternalServerErrorException $exception) {
        $this->errors['api_key'] = HMA_Translations::get('api_internal_error');
        $this->options['api_key'] = null;
        update_option('hear_me_options', $this->options);
      }
    }

    $this->twig->render('includes/settings', [
      'formContent' => $this->renderForm()
    ]);
  }

  public function wizardPage()
  {
    add_submenu_page(
      null,
      'HearMe.ai Setup Wizard',
      'Setup Wizard',
      'manage_options',
      'hear-me-wizard',
      [$this, 'renderWizardPage'],
    );
  }

  public function renderWizardPage()
  {
    $this->twig->render('wizard/wizard', [
      'wizard_title' => 'HeaerMe.Ai Setup Wizard',
      'wizard_titles' => 'Step|Step API key|Player Setup|Custom post setup|All done',
      'formContent' => $this->renderWizardForm(),
      'button_next' => 'Next Step',
      'button_finish' => 'Finish Now',
    ]);
  }

  private function renderForm()
  {
    ob_start();

    settings_fields('hear_me_option_group');
    do_settings_sections('hear-me-settings');
    submit_button();
    $form = ob_get_contents();
    ob_end_clean();

    return $form;
  }

  private function renderWizardForm()
  {
    ob_start();

    $this->twig->render('wizard/partials/setupAPIScreen', [
      'step_title' => 'Please insert below your API key, you can get it on',
      'app_url' => 'https:' . HEAR_ME_SCRIPT_URL . '',
      'app_url_title' => 'HearMe.ai',
      'field' => 'api_key',
      'error' => HMA_Translations::get('api_key_error')
    ]);

    $this->twig->render('wizard/partials/setupPlayer', [
      'step_title' => 'Please set {n} value below and choose where players will be displayed',
      'fieldRadio' => 'insert_type',
      'fieldNumber' => 'insert_number',
      'options' => [
        HMA_Translations::get('before_p') => 'before_p',
        HMA_Translations::get('after_p') => 'after_p',
        HMA_Translations::get('before_h') => 'before_h',
        HMA_Translations::get('after_h') => 'after_h',
      ],
      'assetsPath' => HEAR_ME_ABS_URL . '/assets/img/',
    ]);


    $this->twig->render('wizard/partials/setupCustomPost', [
      'step_title' => 'Please choose which custom post type needs to be supperted',
      'post_types' => get_post_types(array('public' => true, '_builtin' => false), 'objects'),
      'field' => 'supported_posts',
      'options' => get_option('hear_me_options')['supported_posts'] ?? null
    ]);


    $this->twig->render('wizard/partials/setupFinish', [
      'step_title' => 'All Done, now you can exit this wizard and generate firs draft to HearMe.ai',
    ]);

    $form = ob_get_contents();
    ob_end_clean();

    return $form;
  }
}
