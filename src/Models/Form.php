<?php

namespace Leeroy\Forms\Models;

use Leeroy\Forms\Types\FormException;
use Leeroy\Forms\Types\Settings;
use MongoDB\BSON\ObjectId;
use SailCMS\Collection;
use SailCMS\Database\Model;
use SailCMS\Errors\ACLException;
use SailCMS\Errors\DatabaseException;
use SailCMS\Errors\PermissionException;
use SailCMS\Text;
use SailCMS\Types\Dates;

/**
 *
 * @property string $title
 * @property string $handle
 * @property Collection $fields
 * @property Settings $settings
 *
 */
class Form extends Model
{
    protected string $collection = 'form';
    protected array $casting = [
        'settings' => Collection::class
    ];
    public const FIELD_KEY_EXITS = '5002: Field key "%s" already exist.';

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
     * @param Collection $fields
     * @param Settings $settings
     *
     * @return bool
     * @throws FormException
     */
    public function create(string $handle, string $title, Collection $fields, Settings $settings): bool
    {
        if (!$handle) {
            $handle = $title;
        }
        $handle = Text::from($handle)->slug()->value();
        $count = self::query()->count(['handle' => $handle]);

        // Set a number next to the name to make it unique
        if ($count > 0) {
            $handle .= '-' . Text::init()->random(4, false);
        }

        $fieldValidation = $fields->unwrap();

        foreach ($fieldValidation as $key => $field) {
            if (array_key_exists($field, $fieldValidation)) {
                throw new FormException(sprintf(self::FIELD_KEY_EXITS, $field));
            }
        }

        $info = [
            'title' => $title,
            'handle' => $handle,
            'fields' => $fields,
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
     * @param Collection $fields
     * @param Settings $settings
     * @return bool
     *
     * @throws DatabaseException
     */
    public function update(ObjectId|string $id, string $title, string $handle, Collection $fields, Settings $settings): bool
    {
        $_id = $this->ensureObjectId($id);

        if (!$handle) {
            $handle = $title;
        }

        $ifExist = self::getByHandle($handle);

        if ($ifExist && (string)$ifExist->_id !== $id) {
            $handle = Text::from($handle)->slug()->value();
            $count = self::query()->count(['handle' => $handle]);

            // Set a number next to the name to make it unique
            if ($count > 0) {
                $handle .= '-' . Text::init()->random(4, false);
            }
        }

        $update = [
            'title' => $title,
            'handle' => $handle,
            'fields' => $fields,
            'settings' => $settings->castFrom()
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