<?php

use Difra\Ajaxer;
use Difra\Locales;
use Difra\Plugins;
use Difra\Param;
use Difra\Plugins\Users\User;

/**
 * Class AdmUsersListController
 */
class AdmUsersListController extends Difra\Controller\Adm
{
    /** @var \DOMElement */
    private $node = null;

    /**
     * Users list
     * @param Param\NamedPaginator $page
     */
    public function indexAction(\Difra\Param\NamedPaginator $page)
    {
        $this->node = $this->root->appendChild($this->xml->createElement('userList'));
        /** @var \DOMElement $searchNode */
        $searchNode = $this->node->appendChild($this->xml->createElement('search'));
        $search = [];
        if (!empty($_GET['name'])) {
            $searchNode->setAttribute('name', $search['name'] = $_GET['name']);
        }
        User::getListXML($this->node, $page->val(), false, $search);
    }

    /**
     * Edit user (form)
     * @param Param\AnyInt $id
     * @throws Plugins\Users\UsersException
     */
    public function editAction(Param\AnyInt $id)
    {
        $this->node = $this->root->appendChild($this->xml->createElement('userEdit'));
        try {
            $user = User::getById($id->val());
            if (!$user) {
                return;
            }
            $user->getXML($this->node, false);
        } catch (\Exception $ex) {
        }
    }

    /**
     * Edit user (submit)
     * @param Param\AnyInt $id
     * @param Param\AjaxEmail $email
     * @param Param\AjaxCheckbox $change_pw
     * @param Param\AjaxString|null $new_pw
     * @param Param\AjaxData|null $fieldName
     * @param Param\AjaxData|null $fieldValue
     */
    public function saveAjaxAction(
        Param\AnyInt $id,
        Param\AjaxEmail $email,
        Param\AjaxCheckbox $change_pw,
        Param\AjaxString $new_pw = null,
        Param\AjaxData $fieldName = null,
        Param\AjaxData $fieldValue = null
    ) {
        $user = User::getById($id->val());
        $user->setEmail($email->val());
//        $userData['addonFields'] = !is_null($fieldName) ? $fieldName->val() : null;
//        $userData['addonValues'] = !is_null($fieldValue) ? $fieldValue->val() : null;
        if ($change_pw->val() and $new_pw and $new_pw->val()) {
            $user->setPassword($new_pw->val());
            Ajaxer::notify(Locales::get('auth/adm/userDataSavedPassChanged'));
        } else {
            Ajaxer::notify(Locales::get('auth/adm/userDataSaved'));
        }
        Ajaxer::refresh();
    }

    /**
     * Ban user
     * @param Param\AnyInt $id
     * @throws Plugins\Users\UsersException
     */
    public function banAjaxAction(Param\AnyInt $id)
    {
        User::getById($id->val())->setBanned(true);
        Ajaxer::refresh();
    }

    /**
     * Unban user
     * @param Param\AnyInt $id
     * @throws Plugins\Users\UsersException
     */
    public function unbanAjaxAction(Param\AnyInt $id)
    {
        User::getById($id->val())->setBanned(false);
        Ajaxer::refresh();
    }

    /**
     * Manual user activation
     * @param Param\AnyInt $id
     */
    public function activateAjaxAction(Param\AnyInt $id)
    {
        User::getById($id->val())->activateManual();
        Ajaxer::refresh();
    }
}
