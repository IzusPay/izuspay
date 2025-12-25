import { Injectable, NestInterceptor, ExecutionContext, CallHandler } from '@nestjs/common';
import { Observable } from 'rxjs';
import { tap } from 'rxjs/operators';
import { AuditLogsService } from '../../modules/audit-logs/audit-logs.service';

@Injectable()
export class AuditInterceptor implements NestInterceptor {
  constructor(private auditLogsService: AuditLogsService) {}

  intercept(context: ExecutionContext, next: CallHandler): Observable<any> {
    const request = context.switchToHttp().getRequest();
    const { method, url, body, ip, user } = request;

    return next.handle().pipe(
      tap({
        next: (data) => {
          const response = context.switchToHttp().getResponse();
          const statusCode = response.statusCode;

          const safeBody = { ...body };
          if (safeBody.password) safeBody.password = '***';
          if (safeBody.confirmPassword) safeBody.confirmPassword = '***';

          this.auditLogsService.log(
            `${method} ${url}`,
            JSON.stringify({ body: safeBody, statusCode }),
            ip || request.connection.remoteAddress,
            user?.id || null,
            user?.companyId || null
          );
        },
        error: (error) => {
           // Log errors too
           const statusCode = error.status || 500;
           
           const safeBody = { ...body };
           if (safeBody.password) safeBody.password = '***';

           this.auditLogsService.log(
            `${method} ${url}`,
            JSON.stringify({ body: safeBody, statusCode, error: error.message }),
            ip || request.connection.remoteAddress,
            user?.id || null,
            user?.companyId || null
          );
        }
      }),
    );
  }
}
