<x-layouts.site :title="$title" :navKey="$navKey">
    <div class="pb-[clamp(72px,10vw,140px)]">
        <x-ns.page-head :kicker="__('site.pages.blog.kicker')" :title="__('site.pages.blog.title')"
                        :lead="__('site.pages.blog.lead')">

            <x-ns.filter-chips param="category" :active="$activeCategory"
                               :options="collect(['all' => __('site.common.all')])->merge($categories->mapWithKeys(fn ($c) => [$c->slug => $c->t('name')]))->all()" />

            <div class="grid gap-x-8 gap-y-9 md:grid-cols-2 xl:grid-cols-3 mt-9 mb-14">
                @foreach ($posts as $post)
                    <x-ns.post-card :post="$post" />
                @endforeach
            </div>

            {{ $posts->links() }}
        </x-ns.page-head>
    </div>
</x-layouts.site>
