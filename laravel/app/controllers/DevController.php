<?php

/*
 * Here's where we stuff a bunch of test-y sandbox-y dev stuff. 
 *
 * The idea is to only allow users with the Developers role to view it
 */

class DevController extends BaseController
{
    /**
     * Spit out a link for every getMethod in this class (except this 
     * one)
     **/
    public function getIndex()
    {
        $class = new ReflectionClass(__CLASS__);

        ob_start();
        echo "<h3>Methods (Routes) here in " . __CLASS__ . "</h3>";
        echo "<ul>";
        foreach ($class->getMethods() as $method) {
            if (
                ($method->class === __CLASS__) && 
                ($method->name !== explode('::', __METHOD__)[1] ) 
                && preg_match('/^get(.*)$/', $method->name, $matches)
            ) {
                $url = URL::current() . '/' . strtolower($matches[1]);
                echo "<li><a href=\"$url\">$url</a></li>";
            }
        }
        echo "</ul>";

        $content = ob_get_contents();
        ob_end_clean();

        return View::make('pages.plain')->withContent($content);
    }

    public function getRoles()
    {
        ob_start();

        echo "<ul style=\"line-height:20px\">";
        $rolesCollection = Role::all();
        foreach ($rolesCollection as $role) {
            echo "<li><strong>Role:</strong> {$role->name}</li>";

            echo "<ul>";
            if ($role->perms->count() > 0) {
                foreach ($role->perms as $permission) {
                    echo "<li><strong>Permission:</strong> {$permission->name}</li>";
                }
            } else {
                echo "<li><em>Role has no permissions assigned</em></li>";
            }
            echo "</ul>";

        }
        echo '</ul>';

        $content = ob_get_contents();
        ob_end_clean();
        return View::make('pages.plain')->withContent($content);
    }
}
