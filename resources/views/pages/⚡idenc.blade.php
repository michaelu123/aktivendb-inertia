<?php

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Livewire\Component;

new class extends Component {
    public string $url = "";
    public string $remark = "";

    public function mount(Request $request, string $id): void
    {
        $user = Auth::user();
        if (!$user->mayReadHistory) {
            $this->remark = "Du musst mit speziellen Privilegien eingeloggt sein, um diese Seite zu sehen.";
        } else {
            $url = $request->url();
            $x = strpos($url, "/idenc");
            $this->url = substr($url, 0, $x) . "/sb/" . Crypt::encryptString($id);
        }
    }
};
?>

<x-filament::section class="max-w-2xl mx-auto items-center justify-items-center mt-10">
    @if ($this->remark)
        <p>{{ $this->remark }}</p>
    @else
        <a href="{{ $this->url }}" class="underline">Encrypted Url</a>
    @endif
</x-filament::section>