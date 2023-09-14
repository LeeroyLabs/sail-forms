<?php

namespace Leeroy\Forms\Controllers;

use GraphQL\Type\Definition\ResolveInfo;
use Leeroy\Forms\Types\FormDateSearch;
use Leeroy\Forms\Types\FormEntryException;
use SailCMS\Collection;
use SailCMS\Contracts\AppController;
use SailCMS\Errors\DatabaseException;
use SailCMS\GraphQL\Context;
use Leeroy\Forms\Models\FormEntry as FormEntryModel;
use SailCMS\Types\Listing;

class FormEntry extends AppController
{
    /**
     *
     * Get a form layout by id
     *
     * @param mixed $obj
     * @param Collection $args
     * @param Context $context
     * @return FormEntryModel
     * @throws DatabaseException
     * @throws FormEntryException
     */
    public function formEntry(mixed $obj, Collection $args, Context $context): FormEntryModel
    {
        return (new FormEntryModel($args->get('form_handle')))::getById($args->get('id'));
    }

    /**
     *
     * Get all form entries by form handle
     *
     * @param mixed $obj
     * @param Collection $args
     * @param Context $context
     * @return Listing
     * @throws DatabaseException
     * @throws FormEntryException
     */
    public function formEntries(mixed $obj, Collection $args, Context $context): Listing
    {
        $dateSearch = new FormDateSearch(0, 'BEFORE');

        if ($args->get('dateSearch')) {
            $dateSearch = new FormDateSearch($args->get('dateSearch.date', 0), $args->get('dateSearch.operator'));
        }

        $sort = 'name';
        if ($args->get('sort')) {
            $sort =  $args->get('sort');
        }

        return (new FormEntryModel($args->get('form_handle')))->getList(
            $args->get('page'),
            $args->get('limit'),
            $dateSearch,
            $args->get('search', ''),
            $sort,
            ($args->get('order', 1) === 'DESC') ? -1 : 1
        );
    }

    /**
     *
     * Create a form entry
     *
     * @param mixed $obj
     * @param Collection $args
     * @param Context $context
     * @return bool
     * @throws FormEntryException|DatabaseException
     */
    public function createFormEntry(mixed $obj, Collection $args, Context $context): bool
    {
        return (new FormEntryModel($args->get('form_handle')))->create(
            $args->get('form_handle'),
            $args->get('locale'),
            $args->get('template'),
            $args->get('content'),
            $args->get('site_id')
        );
    }

    /**
     *
     * Update a form entry
     *
     * @param mixed $obj
     * @param Collection $args
     * @param Context $context
     * @return bool
     * @throws DatabaseException
     * @throws FormEntryException
     *
     */
    public function updateFormSuccessEmail(mixed $obj, Collection $args, Context $context): bool
    {
        return (new FormEntryModel($args->get('form_handle')))->update(
            $args->get('id'),
            $args->get('form_handle'),
            $args->get('locale'),
            $args->get('title'),
            $args->get('template'),
            $args->get('dates'),
            $args->get('content'),
            $args->get('site_id')
        );
    }

    /**
     *
     * Update a form entry if it's viewed
     *
     * @param mixed $obj
     * @param Collection $args
     * @param Context $context
     * @return bool
     * @throws DatabaseException
     * @throws FormEntryException
     *
     */
    public function viewedFormEntry(mixed $obj, Collection $args, Context $context): bool
    {
        return (new FormEntryModel($args->get('form_handle')))->updateViewed(
            $args->get('id')
        );
    }

    /**
     *
     * Delete a form entry
     *
     * @param mixed $obj
     * @param Collection $args
     * @param Context $context
     * @return bool
     * @throws DatabaseException
     * @throws FormEntryException
     *
     */
    public function deleteFormEntry(mixed $obj, Collection $args, Context $context): bool
    {
        return (new FormEntryModel($args->get('form_handle')))->removeById($args->get('ids')->unwrap());
    }

    public function resolver(mixed $obj, Collection $args, Context $context, ResolveInfo $info): mixed
    {
        if ($info->fieldName === 'content') {
            $contentFormatted = [];
            $obj->content->each(function($key, $value) use (&$contentFormatted)
            {
                $contentFormatted[] = ['key' => $key, 'value' => $value];
            });

            return new Collection($contentFormatted);
        }

        return $obj->{$info->fieldName};
    }
}