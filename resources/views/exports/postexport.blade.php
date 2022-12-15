<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Document</title>
    <style >
        th {
            vertical-align: top !important;
        }
        .thdata {
            font-weight: bold;
            vertical-align: top;
        }
    </style>
</head>


<body>
    <table class="table" style="width: 100%;" >

        @if($postdata)
        <tr>
            <th colspan="7" style="vertical-align: top;text-align: center; background: #eff2f7; "><strong>Post Info</strong></th>
        </tr>
        <tr>
            <th style="vertical-align: top; width: 100px;"><strong>Creator Email</strong></th>
            <th class="thdata" style="vertical-align: top;text-align: center; width: 150px;"><strong>Pre Name</strong></th>
            <th style="vertical-align: top;text-align: center; width: 150px;"><strong>Last Name</strong></th>
                @foreach(get_all_languages() as $language)
                        <th style="vertical-align: top;text-align: center; width: 150px;"><strong>Problem Title {{$language->name}}</strong></th>
                        <th style="vertical-align: top;text-align: center; width: 400px;"><strong>Description {{$language->name}}</strong></th>
                    @endforeach
            <!-- <th style="vertical-align: top;text-align: center; width: 150px;"><strong>Problem Title</strong></th> -->
            <th style="vertical-align: top;text-align: center; width: 150px;"><strong>Hashtag</strong></th>
            <!-- <th style="vertical-align: top;text-align: center; width: 400px;"><strong>Description</strong></th> -->
            <th style="vertical-align: top;text-align: center; width: 400px;"><strong>Problem Images</strong></th>
        </tr>

        <tr>

            <td style="vertical-align: top;text-align:center;">{{$postdata->getUser->email}}</td>
            <td style="vertical-align: top;" >{{$postdata->getUser->first_name}}</td>
            <td style="vertical-align: top;">{{$postdata->getUser->last_name}}</td>
            <!-- <td style="vertical-align: top;">{{$postdata->title}}</td> -->
            @foreach(get_all_languages() as $language)
               
                <td style="vertical-align: top;">{{  @$postdata->languagePost->where('language_unique_name',$language->unique_name)->first()->title }}</td> 
                <td style="vertical-align: top;">{{ @$postdata->languagePost->where('language_unique_name',$language->unique_name)->first()->description}}</td>
            @endforeach

            <td style="vertical-align: top;">{{$postdata->hastag}}</td>
            <!-- <td style="vertical-align: top;">{{$postdata->description}}</td> -->
            <td style="vertical-align: top;">
                @if(count($postdata->getImages))
                @foreach($postdata->getImages as $images)
                    @if(@$loop->first)
                    {{ $images->image_url }}
                    @endif
                @endforeach
                @endif
            </td>
        </tr>

            @if(count($postdata->getImages))
                @foreach($postdata->getImages as $images)
                    @if(@$loop->first)
                    @else
                    <tr>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td style="vertical-align: top;">
                        {{ $images->image_url }}
                        </td>
                    </tr>


                    @endif

                
                @endforeach
                @endif


        @endif

        @if(count($postdata->getSolutionCount))
        <tr>
            <th colspan="7" style="vertical-align: top;text-align: center; background: #eff2f7;"><strong>Solution Info</strong></th>
        </tr>
        <tr>
            <th style="vertical-align: top;text-align: center;"><strong>Pre Name</strong></th>
            <th style="vertical-align: top;text-align: center;"><strong>Last Name</strong></th>
            <th style="vertical-align: top;text-align: center;"><strong>Solution provider Email</strong></th>
            <th style="vertical-align: top;text-align: center;"><strong>Status</strong></th>
            <th style="vertical-align: top;text-align: center;"><strong>Description</strong></th>
            @foreach(get_all_languages() as $language)
                     <th  style="vertical-align: top;text-align: center;"><strong>Description {{$language->name}}</strong></th>
            @endforeach
            <th colspan="2" style="vertical-align: top;text-align: center;"><strong>Solution Link</strong></th>
            
        </tr>
        
         @foreach($postdata->getSolutionCount as $solution)
            <tr>
            
                <td style="vertical-align: top;">{{$solution->getUser->first_name}}</td>
                <td style="vertical-align: top;">{{$solution->getUser->last_name}}</td>
                <td style="vertical-align: top;">{{$solution->getUser->email}}</td>
                <td style="vertical-align: top;">{{$solution->status}}</td>
                <!-- <td style="vertical-align: top;">{{$solution->comment}}</td> -->
                @foreach(get_all_languages() as $language)
                
                    <td style="vertical-align: top;">{{ ($solution->languagePost) ? @$solution->languagePost->where('language_unique_name',$language->unique_name)->first()->text : ''}}</td>
                @endforeach
                <td colspan="2" style="vertical-align: top;">{{$solution->solution_link}}</td>
            </tr>
         @endforeach
        @endif

        @if(count($postdata->getcountMetoo))
        <tr>
            <th colspan="7"></th>
        </tr>

        <tr>
            <th colspan="7"  style="text-align: center; background: #eff2f7;"><strong>Metoo Users</strong></th>
        </tr>
        <tr>
            <th></th>
            <th></th>
            <th></th>
            <th style="text-align: center; vertical-align: top;"><strong>Pre Name</strong></th>
            <th style="text-align: center; vertical-align: top;"><strong>Last Name</strong></th>
            <th style="text-align: center; vertical-align: top;"><strong>NAME</strong></th>
            <th style="text-align: center; vertical-align: top;"><strong>Email</strong></th>
        </tr>
      
       
         @foreach($postdata->getcountMetoo as $metoo)
            <tr>
                <td></td>
                <td></td>
                <td></td>
                <td style="vertical-align: top;">{{$metoo->getUser->first_name}}</td>
                <td style="vertical-align: top;">{{$metoo->getUser->last_name}}</td>
                <td style="vertical-align: top;">{{$metoo->getUser->username}}</td>
                <td style="vertical-align: top;">{{$metoo->getUser->email}}</td>
            </tr>
         @endforeach
        @endif
 </table>

</body>

</html>