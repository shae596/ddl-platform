import { Role } from '@prisma/client';
export interface RequestWithUser {
    user: {
        sub: string;
        email: string;
        role: Role;
    };
}
