import { PrismaService } from '../prisma/prisma.service';
import { Role } from '@prisma/client';
export declare class UsersService {
    private readonly prisma;
    constructor(prisma: PrismaService);
    findByEmail(email: string): any;
    findById(id: string): unknown;
    toPublicUser(user: {
        id: string;
        email: string;
        nom: string;
        prenom: string;
        role: Role;
        service: string | null;
    }): {
        id: string;
        email: string;
        nom: string;
        prenom: string;
        role: Role;
        service: string | null;
    };
}
