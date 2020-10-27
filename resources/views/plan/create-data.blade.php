@if(isset($simplan) && !empty($simplan))
@php unset($simplan->id); @endphp
<table class="table table-hover table-bordered">
    <tbody>
    @foreach($simplan as $skey => $slist)
    @php
    $status     = ($skey == 'status' && $slist == 0) ? 'Inactive' : 'Active';
    @endphp
      <tr>
        <td>{{ ucwords(str_replace("_"," ",$skey)) }}</td>
        @if($skey == 'status')
         <td>{{ $status }}</td>
        @elseif($skey == 'created_at' || $skey == 'updated_at')
        <td>{{ Carbon::parse($slist)->format('d-m-Y H:i:s') }}</td>
        @else
        <td>{{ $slist }}</td>
        @endif
    @endforeach
    </tbody>
  </table>
@endif
@if(isset($switchplan) && !empty($switchplan))
@php unset($switchplan->id); @endphp
<table class="table table-hover table-bordered">
    <tbody>
    @foreach($switchplan as $skey => $slist)
      <tr>
        <td>{{ ucwords(str_replace("_"," ",$skey)) }}</td>
        
        @if($skey == 'created_at' || $skey == 'updated_at')
        <td>{{ Carbon::parse($slist)->format('d-m-Y H:i:s') }}</td>
        @else
        <td>{{ $slist }}</td>
        @endif
    @endforeach
    </tbody>
  </table>
@endif
@if(isset($confplan) && !empty($confplan))
@php unset($confplan->id); @endphp
<table class="table table-hover table-bordered">
    <tbody>
    @foreach($confplan as $ckey => $clist)
    @php
    $status     = ($ckey == 'status' && $clist == 0) ? 'Inactive' : 'Active';
    @endphp
      <tr>
        <td>{{ ucwords(str_replace("_"," ",$ckey)) }}</td>
        @if($ckey == 'status')
         <td>{{ $status }}</td>
        @elseif($ckey == 'description')
        <td>{!! $clist !!}</td>
        @elseif($ckey == 'created_at' || $ckey == 'updated_at')
        <td>{{ Carbon::parse($clist)->format('d-m-Y H:i:s') }}</td>
        @else
        <td>{{ $clist }}</td>
        @endif
    @endforeach
    </tbody>
  </table>
@endif