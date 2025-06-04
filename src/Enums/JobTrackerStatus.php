<?php

namespace Jiannius\JobTracker\Enums;

enum JobTrackerStatus : string
{
    case QUEUED = 'queued';
    case RUNNING = 'running';
    case FINISHED = 'finished';
    case STOPPED = 'stopped'; 
    case FAILED = 'failed'; 
    case EXPIRED = 'expired';
}