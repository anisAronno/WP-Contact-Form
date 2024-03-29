<?php

namespace Saad_Contacts\Frontend;

/**
 * Shortcode handler class
 */
class Shortcode
{
    /**
     * Initializes the class
     */
    public function __construct()
    {
        add_shortcode('saad-contacts', [ $this, 'render_shortcode' ]);
     }

    /**
     * Shortcode handler class
     *
     * @param  array $atts
     * @param  string $content
     *
     * @return string
     */
    public function render_shortcode($atts, $content = '')
    {
         include(dirname(__FILE__) . '/views/saad_contact_form.php');
    }

    public function submit_contact()
    {
        if (!isset($_POST['saad_contacts'])) {
            return;
        }

        if (!wp_verify_nonce($_POST['_wpnonce'], 'saad_contacts')) {
            die(__('Are you cheating?', 'saad_contacts'));
        }

        if (!current_user_can('read')) {
            wp_die(__('Permission Denied!', 'saad_contacts'));
        }


        $page_url = wp_get_referer();

        $name = isset($_POST['name']) ? sanitize_text_field($_POST['name']) : '';
        $email = isset($_POST['email']) ? sanitize_text_field($_POST['email']) : '';
        $phone = isset($_POST['phone']) ? sanitize_text_field($_POST['phone']) : '';
        $subject = isset($_POST['subject']) ? sanitize_text_field($_POST['subject']) : '';
        $message = isset($_POST['message']) ? wp_kses_post($_POST['message']) : '';
        $created_by = isset($_POST['created_by']) ? intval($_POST['created_by']) : 0;
        $errors = [];
        // some basic validation
        if (!$name) {
            $errors[] = __('Error: Name is required', 'saad_contacts');
        }

        // bail out if error found
        if ($errors) {
            $first_error = reset($errors);
            $redirect_to = add_query_arg(array( 'error' => $first_error ), $page_url);
            wp_safe_redirect($redirect_to);
            exit;
        }

        $fields = array(
            'name' => $name,
            'email' => $email,
            'phone' => $phone,
            'subject' => $subject,
            'message' => $message,
            'created_by' => $created_by,
        );


        $insert_id = saad_contact_insert($fields);


        if (is_wp_error($insert_id)) {
            $redirect_to = add_query_arg(array( 'message' => 'error' ), $page_url);
        } else {
            $redirect_to = add_query_arg(array( 'message' => 'success' ), $page_url);
        }

        wp_safe_redirect($redirect_to);

        exit;
    }
}
