@props([
    'jobTracker' => null,
])

@php
$icon = [
    'loading' => '<svg width="24" height="24" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" class="text-zinc-500 size-5" fill="currentColor"><style>.spinner_ajPY{transform-origin:center;animation:spinner_AtaB .75s infinite linear}@keyframes spinner_AtaB{100%{transform:rotate(360deg)}}</style><path d="M12,1A11,11,0,1,0,23,12,11,11,0,0,0,12,1Zm0,19a8,8,0,1,1,8-8A8,8,0,0,1,12,20Z" opacity=".25"/><path d="M10.14,1.16a11,11,0,0,0-9,8.92A1.59,1.59,0,0,0,2.46,12,1.52,1.52,0,0,0,4.11,10.7a8,8,0,0,1,6.66-6.61A1.42,1.42,0,0,0,12,2.69h0A1.57,1.57,0,0,0,10.14,1.16Z" class="spinner_ajPY"/></svg>',
    'success' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="size-6"><path fill-rule="evenodd" d="M2.25 12c0-5.385 4.365-9.75 9.75-9.75s9.75 4.365 9.75 9.75-4.365 9.75-9.75 9.75S2.25 17.385 2.25 12Zm13.36-1.814a.75.75 0 1 0-1.22-.872l-3.236 4.53L9.53 12.22a.75.75 0 0 0-1.06 1.06l2.25 2.25a.75.75 0 0 0 1.14-.094l3.75-5.25Z" clip-rule="evenodd" /></svg>',
    'error' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="size-6"><path fill-rule="evenodd" d="M2.25 12c0-5.385 4.365-9.75 9.75-9.75s9.75 4.365 9.75 9.75-4.365 9.75-9.75 9.75S2.25 17.385 2.25 12Zm13.36-1.814a.75.75 0 1 0-1.22-.872l-3.236 4.53L9.53 12.22a.75.75 0 0 0-1.06 1.06l2.25 2.25a.75.75 0 0 0 1.14-.094l3.75-5.25Z" clip-rule="evenodd" /></svg>',
];
@endphp

@if ($jobTracker)
    <div
    x-data="{
        jobTracker: @js($jobTracker),

        fetch () {
            if (['finished', 'failed'].includes(this.jobTracker.status)) return

            this.$wire.getJobTrackerProgress()
                .then(res => {
                    if (this.jobTracker.status !== res.status) this.$dispatch(res.status, res)
                    Object.assign(this.jobTracker, res)
                })
                .catch(err => console.error(err))
                .finally(() => setTimeout(() => this.fetch(), 2500))
        },
    }"
    x-init="$nextTick(() => fetch())"
    class="space-y-3">
        <div class="flex items-center justify-between gap-3 text-sm">
            <div x-show="jobTracker.status === 'queued'" class="flex items-center gap-2 text-muted">
                {!! $icon['loading'] !!} {{ __('job-tracker::messages.job-is-queued') }}
            </div>

            <div x-show="jobTracker.status === 'finished' && !jobTracker.errors?.length" class="flex items-center gap-2">
                <span class="text-green-500">{!! $icon['success'] !!}</span> {{ __('job-tracker::messages.job-is-completed') }}
            </div>

            <div x-show="jobTracker.status !== 'running' && jobTracker.errors?.length" class="flex items-center gap-2 text-red-500">
                <span class="text-red-500">{!! $icon['error'] !!}</span> {{ __('job-tracker::messages.job-has-errors') }}
            </div>

            <div x-show="jobTracker.status === 'running' && !jobTracker.errors?.length" class="flex items-center gap-2">
                {!! $icon['loading'] !!} {{ __('job-tracker::messages.job-is-running') }}
            </div>
        </div>

        <div class="w-full rounded-full h-3 bg-zinc-100 overflow-hidden">
            <div
            x-bind:style="`width: ${jobTracker.status === 'running' ? jobTracker.progress : 100}%`"
            x-bind:class="{
                'bg-green-500 border-green-500': jobTracker.status === 'finished' && !jobTracker.errors?.length,
                'bg-sky-600 border-sky-600': jobTracker.status === 'running' && jobTracker.progress < 100 && !jobTracker.errors?.length,
                'bg-red-500 border-red-500': jobTracker.errors?.length,
            }"
            class="h-full transition-all duration-500"></div>
        </div>

        <template x-if="jobTracker.errors?.length" hidden>
            <div class="space-y-3 rounded-lg bg-red-50 dark:bg-red-800/20 p-4 text-red-500 dark:text-red-400 text-sm">
                <div>
                    {{ __('job-tracker::messages.there-are-some-errors-while-running-the-job') }}.
                </div>

                <ul class="list-disc list-outside ml-6">
                    <template x-for="err in jobTracker.errors" hidden>
                        <li x-text="err"></li>
                    </template>
                </ul>
            </div>
        </template>
    </div>
@endif