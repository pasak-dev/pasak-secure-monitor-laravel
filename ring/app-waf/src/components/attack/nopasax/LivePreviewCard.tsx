import { useState, useRef, useEffect, useCallback } from "react";
import {
  ExternalLinkIcon,
  EyeIcon,
  Maximize2Icon,
  Minimize2Icon,
  RefreshCwIcon,
  ShieldCheckIcon,
} from "lucide-react";
import { Alert, AlertDescription, AlertTitle } from "@/components/ui/alert";
import { Badge } from "@/components/ui/badge";
import { Button } from "@/components/ui/button";
import {
  Card,
  CardAction,
  CardContent,
  CardDescription,
  CardHeader,
  CardTitle,
} from "@/components/ui/card";
import { Spinner } from "@/components/ui/spinner";
import {
  Tooltip,
  TooltipContent,
  TooltipTrigger,
} from "@/components/ui/tooltip";
import { LAB_BROWSERS } from "@/stores/lab";
import {
  attackerLocationLabel,
  useAttackerLocation,
} from "@/hooks/useAttackerLocation";
import { cn } from "@/lib/utils";
import { PREVIEW_PAGES } from "./constants";
import type { LivePreviewCardProps } from "./types";

export function LivePreviewCard({
  previewUrl,
  previewSrc,
  previewPage,
  previewTick,
  onPageChange,
  onRefresh,
  sendRaw,
  attackerIp,
  browser,
  deviceId,
  busy,
  target,
}: LivePreviewCardProps) {
  const cardRef = useRef<HTMLDivElement>(null);
  const [isFullscreen, setIsFullscreen] = useState(false);
  const [blockCheck, setBlockCheck] = useState<{
    status?: number;
    blocked: boolean;
  } | null>(null);
  const [checking, setChecking] = useState(false);
  const previewLocation = attackerLocationLabel(
    useAttackerLocation(attackerIp),
  );

  const toggleFullscreen = useCallback(async () => {
    const nextState = !isFullscreen;
    setIsFullscreen(nextState);

    try {
      if (nextState) {
        if (
          cardRef.current &&
          !document.fullscreenElement &&
          cardRef.current.requestFullscreen
        ) {
          await cardRef.current.requestFullscreen();
        }
      } else {
        if (document.fullscreenElement && document.exitFullscreen) {
          await document.exitFullscreen();
        }
      }
    } catch {
      // Fallback gracefully ke CSS full-window overlay
    }
  }, [isFullscreen]);

  // Listener tombol Escape
  useEffect(() => {
    const handleKeyDown = (e: KeyboardEvent) => {
      if (e.key === "Escape" && isFullscreen) {
        toggleFullscreen();
      }
    };
    window.addEventListener("keydown", handleKeyDown);
    return () => window.removeEventListener("keydown", handleKeyDown);
  }, [isFullscreen, toggleFullscreen]);

  // Listener perubahan fullscreen native browser
  useEffect(() => {
    const handleFullscreenChange = () => {
      if (!document.fullscreenElement && isFullscreen) {
        setIsFullscreen(false);
      }
    };
    document.addEventListener("fullscreenchange", handleFullscreenChange);
    return () =>
      document.removeEventListener("fullscreenchange", handleFullscreenChange);
  }, [isFullscreen]);

  // Kunci scroll body saat fullscreen
  useEffect(() => {
    if (isFullscreen) {
      const originalOverflow = document.body.style.overflow;
      document.body.style.overflow = "hidden";
      return () => {
        document.body.style.overflow = originalOverflow;
      };
    }
  }, [isFullscreen]);

  /** Cek apakah IP penyerang kini diblokir (GET / memakai IP spoof). */
  const checkAttackerBlocked = async (): Promise<void> => {
    setChecking(true);
    try {
      const res = await sendRaw({
        target,
        path: "/",
        method: "GET",
        ip: attackerIp.trim(),
      });
      setBlockCheck({
        status: res.status,
        blocked:
          res.success === true && (res.status === 403 || res.status === 429),
      });
    } finally {
      setChecking(false);
    }
  };

  return (
    <Card
      ref={cardRef}
      className={cn(
        "transition-all duration-200",
        isFullscreen &&
          "fixed inset-0 z-50 flex h-dvh w-dvw flex-col overflow-hidden rounded-none border-none bg-background p-4 md:p-6 shadow-2xl",
      )}
    >
      <CardHeader className={cn(isFullscreen && "px-0 pt-0 pb-3")}>
        <div className="flex items-center gap-2">
          <CardTitle className="flex items-center gap-2">
            <span>
              👁️ Preview Korban {isFullscreen ? "(Layar Penuh)" : "(live)"}
            </span>
          </CardTitle>
          {isFullscreen && (
            <Badge variant="outline" className="text-[10px] font-normal">
              Tekan ESC untuk keluar
            </Badge>
          )}
        </div>
        <CardAction className="flex items-center gap-1">
          <Tooltip>
            <TooltipTrigger
              render={
                <Button
                  type="button"
                  variant="outline"
                  size="icon-sm"
                  onClick={onRefresh}
                >
                  <RefreshCwIcon data-icon="inline-start" />
                </Button>
              }
            />
            <TooltipContent>Segarkan preview</TooltipContent>
          </Tooltip>
          <Tooltip>
            <TooltipTrigger
              render={
                <Button
                  type="button"
                  variant={isFullscreen ? "secondary" : "outline"}
                  size="icon-sm"
                  onClick={toggleFullscreen}
                >
                  {isFullscreen ? (
                    <Minimize2Icon data-icon="inline-start" />
                  ) : (
                    <Maximize2Icon data-icon="inline-start" />
                  )}
                </Button>
              }
            />
            <TooltipContent>
              {isFullscreen
                ? "Keluar layar penuh (Esc)"
                : "Mode layar penuh (Fullscreen)"}
            </TooltipContent>
          </Tooltip>
        </CardAction>
        <CardDescription className="font-mono break-all text-xs">
          {previewUrl}
        </CardDescription>
        {!isFullscreen && (
          <CardDescription className="font-mono break-all text-xs">
            🎭 {attackerIp} · 🌐{" "}
            {LAB_BROWSERS.find((b) => b.code === browser)?.label ?? browser} ·
            🖥️ {deviceId}
            {previewLocation !== "" && (
              <>
                {" · "}
                <span className="font-sans">🛰️ {previewLocation}</span>
              </>
            )}
          </CardDescription>
        )}
      </CardHeader>
      <CardContent
        className={cn(
          "flex flex-col gap-2",
          isFullscreen && "flex-1 min-h-0 px-0 pb-0 overflow-y-auto",
        )}
      >
        <div className="flex flex-wrap items-center justify-between gap-1 text-[11px]">
          <div className="flex flex-wrap gap-1">
            {PREVIEW_PAGES.map((page) => (
              <Button
                key={page}
                type="button"
                size="sm"
                variant={previewPage === page ? "secondary" : "outline"}
                onClick={() => onPageChange(page)}
                className="font-mono"
              >
                {page}
              </Button>
            ))}
          </div>
          <div className="flex items-center gap-1">
            <Button
              variant="outline"
              size="sm"
              render={
                <a href={previewUrl} target="_blank" rel="noreferrer">
                  <ExternalLinkIcon data-icon="inline-start" />⬈ tab baru
                </a>
              }
            />
            {isFullscreen && (
              <Button
                type="button"
                variant="outline"
                size="sm"
                onClick={toggleFullscreen}
                className="text-xs"
              >
                <Minimize2Icon data-icon="inline-start" />
                Keluar Fullscreen
              </Button>
            )}
          </div>
        </div>
        <div
          className={cn(
            "overflow-hidden rounded-lg bg-white ring-1 ring-foreground/10",
            isFullscreen ? "flex-1 min-h-0 w-full" : "h-[560px] w-full",
          )}
        >
          <iframe
            key={previewSrc}
            src={previewSrc}
            title="Preview aplikasi yang diserang"
            sandbox="allow-forms allow-scripts allow-same-origin allow-popups allow-modals allow-downloads"
            className="h-full w-full bg-white"
          />
        </div>
        <p className="flex items-center gap-1 text-[11px] text-muted-foreground">
          <EyeIcon data-icon="inline-start" />
          Otomatis segar tiap serangan dikirim. Buka <code>/</code> untuk
          Monitor, <code>/upload</code> untuk bukti file masuk.
        </p>
        <div className="flex flex-wrap items-center gap-2">
          <Button
            type="button"
            size="sm"
            variant="outline"
            className="h-auto whitespace-normal py-1.5 text-left"
            onClick={checkAttackerBlocked}
            disabled={busy !== null || checking}
          >
            {checking ? (
              <Spinner data-icon="inline-start" />
            ) : (
              <ShieldCheckIcon data-icon="inline-start" />
            )}
            {checking ? "Mengecek..." : "🛡️ Cek apakah IP penyerang diblokir"}
          </Button>
          {blockCheck !== null &&
            (blockCheck.blocked ? (
              <Badge
                variant="destructive"
                className="h-auto whitespace-normal py-1 text-left"
              >
                IP penyerang DIBLOKIR ({blockCheck.status}) — preview di atas
                ikut menampilkan halaman blokir karena memakai IP yang sama
              </Badge>
            ) : (
              <Badge
                variant="secondary"
                className="h-auto whitespace-normal py-1 text-left"
              >
                IP penyerang BELUM diblokir ({blockCheck.status})
              </Badge>
            ))}
        </div>
        {!isFullscreen && (
          <Alert>
            <ShieldCheckIcon data-icon="inline-start" />
            <AlertTitle>
              Preview kosong, ditolak, atau 419 Page Expired?
            </AlertTitle>
            <AlertDescription className="flex flex-col gap-2">
              <span>
                • <b>Frame ditolak/kosong</b>: Target mengirim{" "}
                <code>X-Frame-Options: SAMEORIGIN</code> atau{" "}
                <code>frame-ancestors</code> (proteksi clickjacking). Serangan
                via tombol di kiri tetap jalan.
              </span>
              <span>
                • <b>419 Page Expired</b>: Terjadi pada pengiriman form / login
                jika browser memblokir cookie sesi lintas origin/port (SameSite
                cookie). Gunakan <b>⬈ tab baru</b> di atas untuk berinteraksi
                atau login langsung di browser.
              </span>
              <span>
                Khusus lab lokal, izinkan bingkai dari konsol ini dengan header
                di aplikasi target:
              </span>
              <code className="break-all">
                Content-Security-Policy: frame-ancestors &apos;self&apos;
                http://localhost:5175
              </code>
            </AlertDescription>
          </Alert>
        )}
      </CardContent>
    </Card>
  );
}
