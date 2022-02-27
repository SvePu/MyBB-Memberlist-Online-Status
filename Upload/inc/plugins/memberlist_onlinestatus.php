<?php

// Disallow direct access to this file for security reasons
if (!defined('IN_MYBB'))
{
    die('Direct initialization of this file is not allowed.');
}

if (defined('THIS_SCRIPT'))
{
    global $templatelist;

    if (isset($templatelist))
    {
        $templatelist .= ',';
    }

    if (THIS_SCRIPT == 'memberlist.php')
    {
        $templatelist .= 'memberlist_user_onlinestatus, memberlist_user_online, memberlist_user_away, memberlist_user_offline';
    }
}

if (!defined('IN_ADMINCP'))
{
    $plugins->add_hook('memberlist_user', 'memberlist_onlinestatus_run');
}

function memberlist_onlinestatus_info()
{
    return array(
        'name'          => 'MyBB Memberlist Online Status',
        'description'   => 'Shows online status of members on memberlist',
        'website'       => 'https://github.com/SvePu/MyBB-Memberlist-Online-Status',
        'author'        => 'SvePu',
        'authorsite'    => 'https://github.com/SvePu',
        'version'       => '1.0',
        'codename'      => 'memberlistonlinestatus',
        'compatibility' => '18*'
    );
}

function memberlist_onlinestatus_activate()
{
    global $db, $mybb;

    $templatearray = array(
        'memberlist_user_onlinestatus' => '<div style="margin-top: 0.5em; font-weight: bold;">{$user[\'onlinestatus\']}</div>',
        'memberlist_user_away' => '<a href="{$user[\'profilelink_plain\']}"><span style="color: tan;">{$lang->postbit_status_away}</span></a>',
        'memberlist_user_offline' => '<span class="offline">{$lang->postbit_status_offline}</span>',
        'memberlist_user_online' => '<a href="online.php"><span class="online">{$lang->postbit_status_online}</span></a>'
    );

    foreach ($templatearray as $name => $template)
    {
        $template = array(
            'title' => $db->escape_string($name),
            'template' => $db->escape_string($template),
            'version' => $mybb->version_code,
            'sid' => -2,
            'dateline' => TIME_NOW
        );

        $db->insert_query('templates', $template);
    }

    require_once MYBB_ROOT . 'inc/adminfunctions_templates.php';
    find_replace_templatesets('memberlist_user', '#' . preg_quote('{$user[\'userstars\']}') . '#', "{\$user['userstars']}\n{\$user['user_onlinestatus']}");
}

function memberlist_onlinestatus_deactivate()
{
    global $db;
    $db->delete_query('templates', "title IN ('memberlist_user_onlinestatus', 'memberlist_user_away', 'memberlist_user_offline', 'memberlist_user_online')");

    require MYBB_ROOT . '/inc/adminfunctions_templates.php';
    find_replace_templatesets('memberlist_user', '#' . preg_quote("\n{\$user['user_onlinestatus']}") . '#', '');
}

function memberlist_onlinestatus_run(&$user)
{
    global $mybb, $templates, $lang;
    $timecut = TIME_NOW - $mybb->settings['wolcutoff'];
    if ($user['lastactive'] > $timecut && ($user['invisible'] != 1 || $mybb->usergroup['canviewwolinvis'] == 1) && $user['lastvisit'] != $user['lastactive'])
    {
        eval("\$user['onlinestatus'] = \"" . $templates->get("memberlist_user_online") . "\";");
    }
    else
    {
        if ($user['away'] == 1 && $mybb->settings['allowaway'] != 0)
        {
            $user['profilelink_plain'] = get_profile_link($user['uid']);
            eval("\$user['onlinestatus'] = \"" . $templates->get("memberlist_user_away") . "\";");
        }
        else
        {
            eval("\$user['onlinestatus'] = \"" . $templates->get("memberlist_user_offline") . "\";");
        }
    }

    eval("\$user['user_onlinestatus'] = \"" . $templates->get("memberlist_user_onlinestatus") . "\";");
}
