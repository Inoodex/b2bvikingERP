@include('errors.layout', [
    'title' => ($title ?? (($status ?? 500) . ' | Error')),
    'badge' => $badge ?? 'Application Error',
    'code' => (string) ($status ?? 500),
    'heading' => $heading ?? 'Something went wrong.',
    'message' => $message ?? 'An unexpected error occurred while processing your request. Please try again.',
    'debug' => $debug ?? null,
    'primaryText' => 'Go To Homepage',
    'primaryUrl' => url('/'),
    'secondaryText' => 'Go Back',
    'secondaryUrl' => url()->previous(),
])
