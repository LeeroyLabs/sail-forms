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
 * @property string       $locale
 * @property bool         $trashed = false
 * @property string       $title
 * @property string       $template
 * @property Dates        $dates
 * @property Collection   $content
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
     * @param string $title
     * @param string $template
     * @param Dates $dates
     * @param Collection $content
     * @return bool
     */
    public function create(string $form_handle, string $locale, string $title, string $template, Dates $dates, Collection $content): bool
    {
        $info = [
            'form_handle' => $form_handle,
            'locale' => $locale,
            'title' => $title,
            'template' => $template,
            'dates' => $dates->castFrom(),
            'content' => $content->castFrom()
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
     * Update a form entry
     *
     * @param ObjectId|string $id
     * @param string $form_handle
     * @param string $locale
     * @param string $title
     * @param string $template
     * @param Dates $dates
     * @param Collection $content
     * @param bool $trashed
     * @return bool
     *
     * @throws DatabaseException
     */
    public function update(ObjectId|string $id, string $form_handle, string $locale, string $title, string $template, Dates $dates, Collection $content, bool $trashed = false): bool
    {
        $_id = $this->ensureObjectId($id);

        $update = [
            'form_handle' => $form_handle,
            'locale' => $locale,
            'title' => $title,
            'template' => $template,
            'dates' => $dates->castFrom(),
            'content' => $content->castFrom(),
            'trashed' => $trashed
        ];

        $this->updateOne(['_id' => $_id], ['$set' => $update]);
        return true;
    }

    /**
     *
     * Delete a form entry
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
     * Get a list of form entries
     *
     * @param int $page
     * @param int $limit
     * @param FormDateSearch|null $dateSearch
     * @param string $search
     * @param string $sort
     * @param int $direction
     * @return Listing
     * @throws ACLException
     * @throws DatabaseException
     * @throws PermissionException
     */
    public function getList(
        int $page = 0,
        int $limit = 25,
        FormDateSearch|null $dateSearch = null,
        string $search = '',
        string $sort = 'name',
        int $direction = Model::SORT_ASC
    ): Listing {
        $this->hasPermissions(true);

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