<div class="view-header">
  <h1>
    <a href="{{ url('/simulation/admin/populations/'.$population->id) }}" style="color: #1877f2;"><i class="fa-solid fa-arrow-left"></i></a>
    {{ $population->name }}
  </h1>
</div>

@include('admin.populations._tabs', ['population' => $population])
