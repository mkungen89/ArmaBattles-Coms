<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Authorize {{ $client->name }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .container {
            background: white;
            border-radius: 16px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            max-width: 500px;
            width: 100%;
            padding: 40px;
        }

        .header {
            text-align: center;
            margin-bottom: 30px;
        }

        .logo {
            width: 80px;
            height: 80px;
            margin: 0 auto 20px;
            background: #4A90E2;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 36px;
            color: white;
            font-weight: bold;
        }

        h1 {
            font-size: 24px;
            color: #333;
            margin-bottom: 10px;
        }

        .subtitle {
            color: #666;
            font-size: 14px;
        }

        .client-name {
            color: #4A90E2;
            font-weight: 600;
        }

        .permissions {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 20px;
            margin: 30px 0;
        }

        .permissions h2 {
            font-size: 16px;
            color: #333;
            margin-bottom: 15px;
        }

        .permission-item {
            display: flex;
            align-items: center;
            margin-bottom: 12px;
            color: #555;
        }

        .permission-item:last-child {
            margin-bottom: 0;
        }

        .permission-item svg {
            width: 20px;
            height: 20px;
            margin-right: 12px;
            color: #4A90E2;
            flex-shrink: 0;
        }

        .user-info {
            background: #e8f4fd;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 30px;
            display: flex;
            align-items: center;
        }

        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: #4A90E2;
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            margin-right: 12px;
        }

        .user-details {
            flex: 1;
        }

        .user-name {
            font-weight: 600;
            color: #333;
        }

        .user-email {
            font-size: 13px;
            color: #666;
        }

        .actions {
            display: flex;
            gap: 12px;
        }

        button {
            flex: 1;
            padding: 14px 24px;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
        }

        .btn-approve {
            background: #4A90E2;
            color: white;
        }

        .btn-approve:hover {
            background: #357ABD;
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(74, 144, 226, 0.4);
        }

        .btn-deny {
            background: #f1f3f5;
            color: #666;
        }

        .btn-deny:hover {
            background: #e9ecef;
        }

        .warning {
            margin-top: 20px;
            padding: 12px;
            background: #fff3cd;
            border-left: 4px solid #ffc107;
            border-radius: 4px;
            font-size: 13px;
            color: #856404;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="logo">AB</div>
            <h1>Authorize Application</h1>
            <p class="subtitle">
                <span class="client-name">{{ $client->name }}</span> wants to access your Arma Battles account
            </p>
        </div>

        <div class="user-info">
            <div class="user-avatar">
                {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
            </div>
            <div class="user-details">
                <div class="user-name">{{ auth()->user()->name }}</div>
                <div class="user-email">{{ auth()->user()->email }}</div>
            </div>
        </div>

        <div class="permissions">
            <h2>This application will be able to:</h2>
            @foreach($scopes as $scope)
                <div class="permission-item">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                    <span>
                        @if($scope === 'profile')
                            Read your profile information (name, username)
                        @elseif($scope === 'email')
                            Read your email address
                        @else
                            {{ ucfirst($scope) }}
                        @endif
                    </span>
                </div>
            @endforeach
        </div>

        <form method="POST" action="{{ route('oauth.authorize') }}">
            @csrf
            <input type="hidden" name="client_id" value="{{ $client->id }}">
            <input type="hidden" name="redirect_uri" value="{{ $redirect_uri }}">
            <input type="hidden" name="scope" value="{{ implode(' ', $scopes) }}">
            <input type="hidden" name="state" value="{{ $state }}">

            <div class="actions">
                <button type="submit" name="approve" value="0" class="btn-deny">
                    Deny
                </button>
                <button type="submit" name="approve" value="1" class="btn-approve">
                    Authorize
                </button>
            </div>
        </form>

        <div class="warning">
            <strong>⚠️ Only authorize if you trust this application.</strong><br>
            By authorizing, you allow {{ $client->name }} to access the information listed above.
        </div>
    </div>
</body>
</html>
