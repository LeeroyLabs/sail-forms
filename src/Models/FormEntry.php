<?php

namespace Leeroy\Forms\Models;

use Leeroy\Forms\Types\FormDateSearch;
use Leeroy\Forms\Types\FormEntryException;
use MongoDB\BSON\ObjectId;
use MongoDB\BSON\Regex;
use SailCMS\Collection;
use SailCMS\Database\Model;
use SailCMS\Errors\ACLException;
use SailCMS\Errors\DatabaseException;
use SailCMS\Errors\PermissionException;
use SailCMS\Types\Dates;
use SailCMS\Types\Listing;
use SailCMS\Types\Pagination;
use SailCMS\Types\QueryOptions;

/**
 *
 * @property string       $form_handle
 * @property ?string      $site_id
 * @property string       $locale
 * @property bool         $trashed = false
 * @property string       $title
 * @property string       $template
 * @property Dates        $dates
 * @property Collection   $content
 * @property bool         $viewed
 *
 */
class FormEntry extends Model
{
    protected string $collection = '';
    protected array $casting = [
        'dates' => Dates::class,
        'content' => Collection::class
    ];

    public const DOES_NOT_EXISTS = '5001: Form handle "%s" does not exists.';
    public const FIELD_KEY_EXITS = '5002: Field key "%s" already exist.';

    /**
     * @throws DatabaseException
     * @throws FormEntryException
     */
    public function __construct(string $handle = '')
    {
        if ($handle && !Form::getByHandle($handle)){
            throw new FormEntryException(sprintf(self::DOES_NOT_EXISTS, $handle));
        }

        if(!$handle){
            $handle = "entry_form";
        }

        $handle = str_replace('-', '_', $handle);

        $this->collection = $handle;

        parent::__construct();
    }

    /**
     *
     * Get a form entry by his ID
     *
     * @param string $id
     * @return Form|null
     *
     * @throws DatabaseException
     *
     */
    public static function getById(string $id): ?FormEntry
    {
        return self::query()->findById($id)->exec();
    }

    /**
     *
     * Create a form entry
     *
     * @param string $form_handle
     * @param string $locale
     * @param string $template
     * @param Collection $content
     * @param string|null $site_id
     * @return bool
     * @throws FormEntryException|DatabaseException
     */
    public function create(string $form_handle, string $locale, string $template, Collection $content, string $site_id = null): bool
    {
        $dates = new Dates(time());
        $form = Form::getByHandle($form_handle);

        $title = $form->settings->entry_title ?? ("New entry for " . $form->title);

        $contentFormatted = [];
        $content->each(function ($key, $value) use (&$contentFormatted)
        {
            if (count($contentFormatted) > 0 && array_key_exists($value->key, $contentFormatted)) {
                throw new FormEntryException(sprintf(self::FIELD_KEY_EXITS, $value->key));
            }
            $contentFormatted[$value->key] = $value->value;
        });

        foreach ($contentFormatted as $key => $value) {
            $title = str_replace('{' . $key . '}', $value, $title);
        }

        $info = [
            'form_handle' => $form_handle,
            'locale' => $locale,
            'title' => $title,
            'template' => $template,
            'dates' => $dates->castFrom(),
            'content' => new Collection($contentFormatted),
            'site_id' => $site_id,
            'viewed' => false
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
     * Get a form entry by his handle
     *
     * @param string $handle
     * @return Form|null
     *
     * @throws DatabaseException
     *
     */
    public static function getByHandle(string $handle): ?FormEntry
    {
        return self::query()->findOne(['handle' => $handle])->exec();
    }

    /**
     *
     * Update a form entry
     *
     * @param ObjectId|string $id
     * @param string $form_handle
     * @param string $locale
     * @param string $title
     * @param string $template
     * @param Dates $dates
     * @param Collection $content
     * @param string|null $site_id
     * @param bool $trashed
     * @return bool
     *
     * @throws DatabaseException|FormEntryException
     *
     */
    public function update(ObjectId|string $id, string $form_handle, string $locale, string $title, string $template, Dates $dates, Collection $content, string $site_id = null, bool $trashed = false): bool
    {
        $_id = $this->ensureObjectId($id);

        $updateDates = new Dates($dates->updated, time());

        $contentFormatted = [];
        $content->each(function ($key, $value) use (&$contentFormatted)
        {
            if (count($contentFormatted) > 0 && array_key_exists($value->key, $contentFormatted)) {
                throw new FormEntryException(sprintf(self::FIELD_KEY_EXITS, $value->key));
            }
            $contentFormatted[$value->key] = $value->value;
        });

        foreach ($contentFormatted as $key => $value) {
            $title = str_replace('{' . $key . '}', $value, $title);
        }

        $update = [
            'form_handle' => $form_handle,
            'locale' => $locale,
            'title' => $title,
            'template' => $template,
            'dates' => $updateDates->castFrom(),
            'content' => new Collection($contentFormatted),
            'trashed' => $trashed,
            'site_id' => $site_id
        ];

        $this->updateOne(['_id' => $_id], ['$set' => $update]);
        return true;
    }

    /**
     *
     * Update if form entry is viewed
     *
     * @param ObjectId|string $id
     * @return bool
     *
     * @throws DatabaseException
     *
     */
    public function updateViewed(ObjectId|string $id): bool
    {
        $_id = $this->ensureObjectId($id);

        $update = [
            'viewed' => true
        ];

        $this->updateOne(['_id' => $_id], ['$set' => $update]);
        return true;
    }

    /**
     *
     * Delete form entry
     *
     * @param array $ids
     * @return bool
     *
     * @throws DatabaseException
     *
     */
    public function removeById(array $ids): bool
    {
        $ids = $this->ensureObjectIds($ids, true);
        $this->deleteMany(['_id' => ['$in' => $ids]]);
        return true;
    }

    /**
     *
     * Get a list of form entries
     *
     * @param int $page
     * @param int $limit
     * @param FormDateSearch|null $dateSearch
     * @param string $search
     * @param string $sort
     * @param int $direction
     * @return Listing
     * @throws DatabaseException
     */
    public function getList(
        int $page = 0,
        int $limit = 25,
        FormDateSearch|null $dateSearch = null,
        string $search = '',
        string $sort = 'name',
        int $direction = Model::SORT_ASC
    ): Listing {
        $offset = $page * $limit - $limit;

        $options = QueryOptions::initWithSort([$sort => $direction]);
        $options->skip = $offset;
        $options->limit = $limit;

        // Collation set for sorting
        $options->collation = 'en';

        $query = [];

        if ($search !== '') {
            $query['title'] = new Regex($search, 'gi');
        }

        if ($dateSearch && $dateSearch->date > 0) {
            $query['dates.created'] = $dateSearch->date;

            if ($dateSearch->operator === "BEFORE") {
                $query['dates.created'] = ['$lte' => $dateSearch->date];
            }

            if ($dateSearch->operator === "AFTER") {
                $query['dates.created'] = ['$gte' => $dateSearch->date];
            }
        }

        // Pagination
        $total = $this->count($query);
        $pages = ceil($total / $limit);
        $current = $page;
        $pagination = new Pagination($current, $pages, $total);

        $list = $this->find($query, $options)->exec();

        return new Listing($pagination, new Collection($list));
    }
}