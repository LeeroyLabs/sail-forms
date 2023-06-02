<?php

namespace Leeroy\Forms\Models;

use Leeroy\Forms\Types\Settings;
use MongoDB\BSON\ObjectId;
use SailCMS\Collection;
use SailCMS\Database\Model;
use SailCMS\Errors\ACLException;
use SailCMS\Errors\DatabaseException;
use SailCMS\Errors\PermissionException;

/**
 *
 * @property string $title
 * @property string $handle
 * @property string $form_layout_id
 * @property Settings $settings
 *
 */
class Form extends Model
{
    protected string $collection = 'form_type';
    protected array $casting = [
        'settings' => Collection::class
    ];

    /**
     *
     * Get a formType by his ID
     *
     * @param string $id
     * @return Form|null
     *
     * @throws DatabaseException
     *
     */
    public static function getById(string $id): ?Form
    {
        return self::query()->findById($id)->exec();
    }

    /**
     *
     * Get a formType by his handle
     *
     * @param string $handle
     * @return Form|null
     *
     * @throws DatabaseException
     *
     */
    public static function getByHandle(string $handle): ?Form
    {
        return self::query()->findOne(['handle' => $handle])->exec();
    }

    /**
     *
     * Create a formType
     *
     * @param string $title
     * @param string $handle
     * @param ObjectId|string $form_layout_id
     * @param Settings $settings
     * @return bool
     */
    public function create(string $handle, string $title, ObjectId|string $form_layout_id, Settings $settings): bool
    {
        if (!is_string($form_layout_id)) {
            $form_layout_id = (string)$form_layout_id;
        }

        $info = [
            'title' => $title,
            'handle' => $handle,
            'form_layout_id' => $form_layout_id,
            'settings' => $settings->castFrom()
        ];

        try {
            $this->insert($info);
            return true;
        } catch (DatabaseException $e) {
            return false;
        }
    }

    /**
     *
     * Update a formType
     *
     * @param ObjectId|string $id
     * @param string $title
     * @param string $handle
     * @param ObjectId|string $form_layout_id
     * @param Settings $settings
     * @return bool
     *
     * @throws DatabaseException
     *
     */
    public function update(ObjectId|string $id, string $title, string $handle, ObjectId|string $form_layout_id, Settings $settings): bool
    {
        $_id = $this->ensureObjectId($id);

        if (!is_string($form_layout_id)) {
            $form_layout_id = (string)$form_layout_id;
        }

        $update = [
            'title' => $title,
            'handle' => $handle,
            'form_layout_id' => $form_layout_id,
            $settings->castFrom()
        ];

        $this->updateOne(['_id' => $_id], ['$set' => $update]);
        return true;
    }

    /**
     *
     * Delete a form type
     *
     * @param string $id
     * @return bool
     *
     * @throws DatabaseException
     *
     */
    public function removeById(string $id): bool
    {
        $this->deleteById($id);
        return true;
    }

    /**
     *
     * Get list of formType
     *
     * @param bool $api
     * @return Collection
     * @throws ACLException
     * @throws DatabaseException
     * @throws PermissionException
     */
    public function getList(bool $api = false): Collection
    {
        if ($api) {
            (new static())->hasPermissions(true);
        }

        $instance = new static();
        return new Collection($instance->find([])->exec());
    }
}