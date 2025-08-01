<h2>Print Settings</h2>
<a href="{{ route('print-settings.create') }}">Add</a>


@if(session('success'))
    <p>{{ session('success') }}</p>
@endif

<table border="1">
    <thead>
        <tr>
            <th>Paper Size</th>
            <th>Color</th>
            <th>Price</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        @forelse ($settings as $setting)
            <tr>
                <td>{{ $setting->paper_size }}</td>
                <td>{{ $setting->color_option }}</td>
                <td>{{ $setting->price }}</td>
                <td>
                    <form action="{{ route('print-settings.destroy', $setting) }}" method="POST">
                        @csrf
                        @method('DELETE')
                        <button type="submit" onclick="return confirm('Delete this setting?')">Delete</button>
                    </form>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="4">No settings found.</td>
            </tr>
        @endforelse
    </tbody>
</table>
