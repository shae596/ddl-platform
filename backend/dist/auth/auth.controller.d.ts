import { AuthService } from './auth.service';
import { LoginDto } from './dto/login.dto';
import type { RequestWithUser } from './types/request-with-user';
export declare class AuthController {
    private readonly authService;
    constructor(authService: AuthService);
    login(dto: LoginDto): unknown;
    me(req: RequestWithUser): unknown;
}
