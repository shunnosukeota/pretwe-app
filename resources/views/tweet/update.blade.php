<x-layout title="編集 | プレツイ">
    <x-layout.single>
        <div class="px-2 pt-20">
            
            @php
                $breadcrumbs = [
                    ['href' => route('tweet.index'), 'label' => 'TOP'],
                    ['href' => '#', 'label' => '編集']
                ];
            @endphp
            <x-element.breadcrumbs :breadcrumbs="$breadcrumbs"></x-element.breadcrumbs>
            <x-tweet.form.put :tweet="$tweet"></x-tweet.form.put>
    </x-layout.single>
</x-layout>