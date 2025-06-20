@php
$id = $attributes->get('id');
$uuid = $attributes->get('uuid');
$name = $attributes->get('name');
$user = $attributes->get('user');
$user = is_string($user) || is_numeric($user) ? \App\Models\User::find($user) : $user;
@endphp

<div
x-cloak
x-data="{
    counter: null,
    tracker: null,

    get isLoading () {
        return this.counter === 0
    },

    get isShow () {
        return !this.isLoading
            && this.tracker
            && (
                !this.isFinished
                || this.counter > 1
                || this.hasErrors
                || this.isDownloadable
            )
    },

    get isQueued () {
        return this.tracker?.status === @js(\Jiannius\JobTracker\Enums\JobTrackerStatus::QUEUED->value)
    },

    get isRunning () {
        return this.tracker?.status === @js(\Jiannius\JobTracker\Enums\JobTrackerStatus::RUNNING->value)
            && this.tracker?.progress >= 0
            && this.tracker?.progress < 100
    },

    get isFinished () {
        return this.tracker?.status === @js(\Jiannius\JobTracker\Enums\JobTrackerStatus::FINISHED->value)
            || this.tracker?.status === @js(\Jiannius\JobTracker\Enums\JobTrackerStatus::FAILED->value)
    },

    get isDownloadable () {
        return this.tracker?.is_downloadable
    },

    get hasErrors () {
        return this.tracker && !empty(this.tracker.errors)
    },

    get csrfToken () {
        return document.querySelector(`meta[name='csrf-token']`).getAttribute('content')
    },

    getJobTracker (delay = 0) {
        if (this.counter === null) return

        setTimeout(() => {
            const formdata = new FormData();
            formdata.append('id', @js($id ?? ''));
            formdata.append('uuid', @js($uuid ?? ''));
            formdata.append('name', @js($name ?? ''));
            formdata.append('user', @js($user?->id ?? ''));
            
            return fetch('/__job-tracker', {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': this.csrfToken },
                body: formdata,
            }).then(res => (res.json())).then(res => {
                if (empty(res)) return this.reset()

                this.tracker = res

                if (this.counter !== null) this.counter++
                if (!this.isFinished) this.$nextTick(() => this.getJobTracker(2500))
            })
        }, delay)
    },

    deleteJobTracker () {
        if (!this.tracker?.id) return
        if (!this.hasErrors && !this.isDownloadable) return

        return fetch('/__job-tracker/'+this.tracker.id, {
            method: 'DELETE',
            headers: { 'X-CSRF-TOKEN': this.csrfToken },
        })
    },

    exitJobTracker () {
        this.deleteJobTracker()
        this.reset()
    },

    resetJobTracker () {
        this.tracker = null
        this.counter = null
    },
}"
x-on:get-job-tracker.window="() => { counter = 0; getJobTracker(); }"
x-on:reset-job-tracker.window="resetJobTracker()"
x-init="$nextTick(() => $dispatch('get-job-tracker'))">
    <template x-if="isLoading" hidden>
        <svg role="img" width="340" height="84" aria-labelledby="loading-aria" viewBox="0 0 340 84" preserveAspectRatio="none">
            <title id="loading-aria">Loading...</title>
            <rect x="0" y="0" width="100%" height="100%" clip-path="url(#clip-path)" style='fill: url("#fill");'></rect>
            <defs>
                <clipPath id="clip-path">
                    <rect x="0" y="0" rx="6" ry="6" width="67" height="11" />
                    <rect x="76" y="0" rx="6" ry="6" width="140" height="11" />
                    <rect x="127" y="48" rx="6" ry="6" width="53" height="11" />
                    <rect x="187" y="48" rx="6" ry="6" width="72" height="11" />
                    <rect x="18" y="48" rx="6" ry="6" width="100" height="11" />
                    <rect x="0" y="71" rx="6" ry="6" width="70" height="11" />
                    <rect x="73" y="71" rx="6" ry="6" width="120" height="11" />
                    <rect x="18" y="23" rx="6" ry="6" width="140" height="11" />
                    <rect x="166" y="23" rx="6" ry="6" width="103" height="11" />
                </clipPath>
                <linearGradient id="fill">
                    <stop offset="0.599964" stop-color="#f3f3f3" stop-opacity="1">
                        <animate attributeName="offset" values="-2; -2; 1" keyTimes="0; 0.25; 1" dur="2s" repeatCount="indefinite"></animate>
                    </stop>
                    <stop offset="1.59996" stop-color="#ecebeb" stop-opacity="1">
                        <animate attributeName="offset" values="-1; -1; 2" keyTimes="0; 0.25; 1" dur="2s" repeatCount="indefinite"></animate>
                    </stop>
                    <stop offset="2.59996" stop-color="#f3f3f3" stop-opacity="1">
                        <animate attributeName="offset" values="0; 0; 3" keyTimes="0; 0.25; 1" dur="2s" repeatCount="indefinite"></animate>
                    </stop>
                </linearGradient>
            </defs>
        </svg>
    </template>

    <template x-if="isShow" hidden>
        <div class="space-y-6">
            <div class="space-y-2 text-sm text-muted">
                <template x-if="isQueued" hidden>
                    <div class="flex items-center gap-2">
                        <svg width="24" height="24" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" class="text-zinc-500 size-5" fill="currentColor"><style>.spinner_ajPY{transform-origin:center;animation:spinner_AtaB .75s infinite linear}@keyframes spinner_AtaB{100%{transform:rotate(360deg)}}</style><path d="M12,1A11,11,0,1,0,23,12,11,11,0,0,0,12,1Zm0,19a8,8,0,1,1,8-8A8,8,0,0,1,12,20Z" opacity=".25"/><path d="M10.14,1.16a11,11,0,0,0-9,8.92A1.59,1.59,0,0,0,2.46,12,1.52,1.52,0,0,0,4.11,10.7a8,8,0,0,1,6.66-6.61A1.42,1.42,0,0,0,12,2.69h0A1.57,1.57,0,0,0,10.14,1.16Z" class="spinner_ajPY"/></svg>
                        {{ __('job-tracker::messages.job-is-queued') }}
                    </div>
                </template>

                <template x-if="isRunning && tracker.progress < 100" hidden>
                    <div class="flex items-center gap-2">
                        <svg width="24" height="24" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" class="text-zinc-500 size-5" fill="currentColor"><style>.spinner_ajPY{transform-origin:center;animation:spinner_AtaB .75s infinite linear}@keyframes spinner_AtaB{100%{transform:rotate(360deg)}}</style><path d="M12,1A11,11,0,1,0,23,12,11,11,0,0,0,12,1Zm0,19a8,8,0,1,1,8-8A8,8,0,0,1,12,20Z" opacity=".25"/><path d="M10.14,1.16a11,11,0,0,0-9,8.92A1.59,1.59,0,0,0,2.46,12,1.52,1.52,0,0,0,4.11,10.7a8,8,0,0,1,6.66-6.61A1.42,1.42,0,0,0,12,2.69h0A1.57,1.57,0,0,0,10.14,1.16Z" class="spinner_ajPY"/></svg>
                        <span x-text="`${tracker.progress}%`"></span>
                    </div>
                </template>

                <template x-if="isFinished && !hasErrors" hidden>
                    <div class="flex items-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" class="text-green-500 size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-green-500 size-5"><path d="M20 6 9 17l-5-5"/></svg>
                        {{ __('job-tracker::messages.completed') }}
                    </div>
                </template>

                <template x-if="isFinished && hasErrors" hidden>
                    <div class="flex items-center gap-2 text-red-500">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-triangle-alert-icon lucide-triangle-alert"><path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3"/><path d="M12 9v4"/><path d="M12 17h.01"/></svg>
                        {{ __('job-tracker::messages.job-errors') }}
                    </div>
                </template>

                <div class="w-full rounded-full h-3 bg-zinc-100 overflow-hidden">
                    <div
                    x-bind:style="`width: ${isQueued ? 0 : tracker.progress}%`"
                    x-bind:class="{
                        'bg-green-500': tracker.progress >= 100,
                        'bg-red-500': hasErrors,
                        'bg-accent': tracker.progress < 100 && empty(tracker.errors),
                    }"
                    class="h-full bg-accent transition-all duration-500"></div>
                </div>

                <div class="flex items-center gap-2">
                    <div x-text="tracker.filename" x-show="tracker.filename" class="grow truncate"></div>

                    <template x-if="isDownloadable" hidden>
                        <a x-bind:href="`/__job-tracker/download/${tracker.id}`" class="text-sm text-blue-500 underline decoration-dotted inline-flex items-center gap-2">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-download-icon lucide-download"><path d="M12 15V3"/><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><path d="m7 10 5 5 5-5"/></svg>
                            {{ __('job-tracker::messages.download') }}
                        </a>
                    </template>
                </div>
            </div>

            <template x-if="hasErrors" hidden>
                <div class="space-y-3 rounded-lg bg-red-50 p-4 text-red-500 text-sm">
                    <div>
                        {{ __('job-tracker::messages.there-are-some-errors-while-running-the-job') }}.
                    </div>

                    <ul class="list-disc list-outside ml-6">
                        <template x-for="err in tracker.errors" hidden>
                            <li x-text="err"></li>
                        </template>
                    </ul>
                </div>
            </template>

            <template x-if="isFinished" hidden>
                <button type="button" class="text-sm text-blue-500 underline decoration-dotted inline-flex items-center gap-2" x-on:click="exitJobTracker()">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-arrow-left-icon lucide-arrow-left"><path d="m12 19-7-7 7-7"/><path d="M19 12H5"/></svg>
                    {{ __('job-tracker::messages.exit-job') }}
                </button>
            </template>
        </div>
    </template>

    <template x-if="!isLoading &&!isShow" hidden>
        <div {{ $attributes->except(['id', 'uuid', 'name', 'user']) }}>
            {{ $slot }}
        </div>
    </template>
</div>