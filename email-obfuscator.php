<?php
/*
Plugin Name: Email Obfuscator
Plugin URI:  http://chasedouglas.consulting/
Description: A lightweight plugin to obfuscate email addresses and protect them from spam bots.
Version:     1.0
Author:      Chase Douglas
Author URI:  http://chasedouglas.consulting
License:     GPL2
License URI: https://www.gnu.org/licenses/gpl-2.0.html
*/

// Code to hide emails from bots
function encrypt_email($email)
{
    return base64_encode($email);
}

function generate_decrypt_script($encrypted_email)
{
    return 'DeCryptX(\'' . $encrypted_email . '\')';
}

function generate_mailto_link($email)
{
    $encrypted_email = encrypt_email($email);
    $javascript_code = generate_decrypt_script($encrypted_email);
    // Ensure only the JavaScript call is being used, without additional HTML nesting
    return '<a href="javascript:' . htmlspecialchars($javascript_code) . '">' . htmlspecialchars($email) . '</a>';
}

function obfuscate_emails($content)
{
    // Create a new DOMDocument and load the content
    $dom = new DOMDocument();
    @$dom->loadHTML(mb_convert_encoding($content, 'HTML-ENTITIES', 'UTF-8'), LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
    $links = $dom->getElementsByTagName('a');

    foreach ($links as $link) {
        // Check if the link is a mailto link
        $href = $link->getAttribute('href');
        if (strpos($href, 'mailto:') === 0) {
            $email = substr($href, 7); // Remove the 'mailto:' part
            $encryptedEmail = encrypt_email($email);
            $decryptedLink = generate_decrypt_script($encryptedEmail);

            // Set the new href to call the JavaScript decrypt function
            $link->setAttribute('href', 'javascript:' . $decryptedLink);
            // Optionally, set the display text to the encrypted email or leave it as is
            // $link->nodeValue = $encryptedEmail;
        }
    }

    // Save the modified content
    return $dom->saveHTML();
}
add_filter('the_content', 'obfuscate_emails');


function email_obfuscator_enqueue_scripts()
{
    wp_enqueue_script('email-decrypt', plugin_dir_url(__FILE__) . 'js/decrypt.js', array(), '1.0', true);
}
add_action('wp_enqueue_scripts', 'email_obfuscator_enqueue_scripts');