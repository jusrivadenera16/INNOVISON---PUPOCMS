<div class="clearance-source-fields clearance-health-category-fields" data-health-category-fields hidden>
    <label class="clearance-form-label">
        Health form category to count
        <select name="source_category_id" class="form-control" data-health-category-select disabled>
            <option value="">Select a health form category</option>
            @foreach($healthFormCategories as $category)
                <option
                    value="{{ $category->id }}"
                    data-audiences="{{ implode(',', $category->available_for ?? []) }}"
                    {{ (string) ($selectedCategoryId ?? '') === (string) $category->id ? 'selected' : '' }}
                >{{ $category->name }}</option>
            @endforeach
        </select>
    </label>
    <small class="clearance-data-source-help">Only approved records in this category and its matching review workflow are counted.</small>
</div>
