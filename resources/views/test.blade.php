<!DOCTYPE html>
<html>
    <head>
        <title></title>
    </head>
    <body>
        <center>
            <p>{{ $Laravel }}</p>

            <table style="border: 1px #000;">
                <thead>
                    <tr>
                        <th>firstname</th>
                        <th>lastname</th>
                        <th>Email</th>
                        <th>image</th>
                        <th>role</th>
                        <th>qrcode</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($users as $user)
                        <tr>
                            <td>{{ $user->firstname }}</td>
                            <td>{{ $user->firstname }}</td>
                            <td>{{ $user->email }}</td>
                            <td><img src="{{ asset( 'storage/'.$user->image) }}" width="100" height="70" alt="User Image"></td>
                            <td>{{ $user->role }}</td>
                            <td><img src="{{ asset('storage/users/qrcodes/'.$user->id.'.svg') }}" width="100" height="70" alt=""></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </center>
    </body>
</html>
