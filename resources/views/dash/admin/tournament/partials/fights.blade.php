@foreach($championship->fights()->get()->groupBy('area') as $fightsByArea)
    <h4 class="text-lg font-semibold mb-4 ">Area {{ $fightsByArea->get(0)->area }}</h4>
    <div class="overflow-x-auto">
        <table class="w-full text-left text-sm border border-gray-700 rounded-md overflow-hidden">
            <thead class="bg-[#2c2c2c] text-gray-300">
                <tr>
                    <th class="px-4 py-2 border-b border-gray-300 text-center text-sm font-medium  w-1/6">Id</th>
                    <th class="px-4 py-2 border-b border-gray-300 text-center text-sm font-medium  w-5/12">Competitor 1</th>
                    <th class="px-4 py-2 border-b border-gray-300 text-center text-sm font-medium  w-5/12">Competitor 2</th>
                </tr>
            </thead>
            <tbody>
                <?php $fightId = 0; ?>
                @foreach($fightsByArea as $fight)
                    @if ($fight->shouldBeInFightList(false))
                        <?php
                        $fighter1 = optional($fight->fighter1)->fullName ?? "BYE";
                        $fighter2 = optional($fight->fighter2)->fullName ?? "BYE";
                        $fightId++;
                        ?>
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-2 border-b border-gray-200 text-center ">{{$fightId}}</td>
                            <td class="px-4 py-2 border-b border-gray-200 text-center ">{{ $fighter1 }}</td>
                            <td class="px-4 py-2 border-b border-gray-200 text-center ">{{ $fighter2 }}</td>
                        </tr>
                    @endif
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="mb-8"></div> {{-- This acts as your <br/><br/> --}}
@endforeach
