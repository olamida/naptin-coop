@props(['memberOptions' => [], 'name' => 'member_id', 'selectedId' => '', 'showBalance' => false, 'placeholder' => 'Type to search members...'])

<x-member-combobox :members="$memberOptions" :name="$name" :selected-id="$selectedId" :show-balance="$showBalance" :placeholder="$placeholder" />
