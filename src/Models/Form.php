<?php

namespace Leeroy\Forms\Models;

use Leeroy\Forms\Types\Settings;
use MongoDB\BSON\ObjectId;
use SailCMS\Collection;
use SailCMS\Database\Model;
use SailCMS\Errors\ACLException;
use SailCMS\Errors\DatabaseException;
use SailCMS\Errors\PermissionException;
use SailCMS\Types\Dates;

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
    protected string $collection = 'form';
    protected array $casting = [
        'settings' => Collection::class
    ];

    /**
     *
     * Get a form by his ID
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
     * Get a form by his handle
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
     * Create a form
     *
     * @param string $handle
     * @param string $title
     * @param ObjectId|string $form_layout_id
     * @param Settings $settings
     *
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
     * Update a form
     *
     * @param ObjectId|string $id
     * @param string $title
     * @param string $handle
     * @param ObjectId|string $form_layout_id
     * @param Settings $settings
     * @return bool
     *
     * @throws DatabaseException
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
     * Delete form(s)
     *
     * @param array $ids
     * @return bool
     *
     * @throws DatabaseException
     *
     */
    public function removeByIds(array $ids): bool
    {
        $ids = $this->ensureObjectIds($ids, true);
        $this->deleteMany(['_id' => ['$in' => $ids]]);
        return true;
    }

    /**
     *
     * Get list of forms
     *
     * @param bool $api
     * @return Collection
     *
     * @throws ACLException
     * @throws DatabaseException
     * @throws PermissionException
     *
     */
    public function getList(bool $api = false): Collection
    {
        if ($api) {
            (new static())->hasPermissions(true);
        }

        $instance = new static();
        return new Collection($instance->find([])->exec());
    }

    /**
     *
     * Create custom success email
     *
     * @param string $form_handle
     * @param string $locale
     * @param string $title
     * @param string $template
     * @param Collection $content
     * @param string|null $siteId
     * @return bool
     */
    public function createSuccessEmail(string $form_handle, string $locale, string $title, string $template, Collection $content, string $siteId = null):bool
    {
        return (new FormEntry('success_email'))->create(
            $form_handle,
            $locale,
            $title,
            $template,
            $content,
            $siteId
        );
    }

    /**
     *
     * Update custom success email
     *
     * @param ObjectId|string $id
     * @param string $form_handle
     * @param string $locale
     * @param string $title
     * @param string $template
     * @param Dates $dates
     * @param Collection $content
     * @param string|null $siteId
     * @param bool $trashed
     * @return bool
     *
     * @throws DatabaseException
     */
    public function updateSuccessEmail(ObjectId|string $id, string $form_handle, string $locale, string $title, string $template, Dates $dates, Collection $content, string $siteId = null, bool $trashed = false):bool
    {
        $updateDates = new Dates($dates->updated, time());

        return (new FormEntry('success_email'))->update(
            $id,
            $form_handle,
            $locale,
            $title,
            $template,
            $updateDates,
            $content,
            $siteId
        );
    }

    /**
     *
     * Remove success email handle
     *
     * @param string $handle
     * @return bool
     *
     * @throws DatabaseException
     */
    public function removeSuccessEmailByHandle(string $handle): bool
    {
        $this->deleteOne(['handle' => $handle]);
        return true;
    }

    /**
     *
     * Get success email by handle
     *
     * @param string $form_handle
     * @return FormEntry
     *
     * @throws DatabaseException
     *
     */
    public function getSuccessEmailByHandle(string $form_handle = "default"):FormEntry
    {
        return (new FormEntry('success_email'))::getByHandle($form_handle);
    }
}