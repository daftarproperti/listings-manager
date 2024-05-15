<h1>View Test Cases</h1>

<ul>
@foreach ($pages as $page)
<li><a href="/view-test/{{$page}}">{{$page}}</a></li>
@endforeach
</ul>
