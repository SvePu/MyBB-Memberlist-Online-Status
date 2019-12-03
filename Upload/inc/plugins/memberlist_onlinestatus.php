<?php

// Disallow direct access to this file for security reasons
if(!defined('IN_MYBB'))
{
	die('Direct initialization of this file is not allowed.');
}

if(defined('THIS_SCRIPT'))
{
    global $templatelist;

    if(isset($templatelist))
    {
        $templatelist .= ',';
    }

	if(THIS_SCRIPT== 'memberlist.php')
	{
		$templatelist .= 'postbit_online, postbit_away, postbit_offline';
	}
}

function memberlist_onlinestatus_info()
{
	return array(
		'name'			=> 'MyBB Memberlist Online Status',
		'description'	=> 'Shows online status of members on memberlist',
		'website'		=> 'https://github.com/SvePu/MyBB-Memberlist-Online-Status',
		'author'		=> 'SvePu',
		'authorsite'	=> 'https://github.com/SvePu',
		'version'		=> '1.0',
		'codename'		=> 'memberlistonlinestatus',
		'compatibility' => '18*'
	);
}

function memberlist_onlinestatus_activate()
{
	require_once MYBB_ROOT.'inc/adminfunctions_templates.php';
	find_replace_templatesets('memberlist_user', '#'.preg_quote('{$user[\'profilelink\']}').'#', "{\$user['profilelink']}{\$user['onlinestatus']}");
}

function memberlist_onlinestatus_deactivate()
{
	require MYBB_ROOT.'/inc/adminfunctions_templates.php';
	find_replace_templatesets('memberlist_user', '#'.preg_quote('{$user[\'onlinestatus\']}').'#', '');
}

function memberlist_onlinestatus_run(&$user)
{
	global $mybb, $theme, $templates, $lang;
	$lang->load('global');
	$timecut = TIME_NOW - $mybb->settings['wolcutoff'];
	if($user['lastactive'] > $timecut && ($user['invisible'] != 1 || $mybb->usergroup['canviewwolinvis'] == 1) && $user['lastvisit'] != $user['lastactive'])
	{
		eval("\$user['onlinestatus'] = \"".$templates->get("postbit_online")."\";");
	}
	else
	{
		if($user['away'] == 1 && $mybb->settings['allowaway'] != 0)
		{
			eval("\$user['onlinestatus'] = \"".$templates->get("postbit_away")."\";");
		}
		else
		{
			eval("\$user['onlinestatus'] = \"".$templates->get("postbit_offline")."\";");
		}
	}
}
$plugins->add_hook('memberlist_user', 'memberlist_onlinestatus_run');
