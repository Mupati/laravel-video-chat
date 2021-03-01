<template>
  <div class="container">
    <div class="row">
      <div class="col-md-8 offset-md-2">
        <button class="btn btn-success" @click="joinBroadcast">
          Join Stream</button
        ><br />

        <video autoplay ref="viewer"></video>
      </div>
    </div>
  </div>
</template>

<script>
import Peer from "simple-peer";
export default {
  name: "Viewer",
  props: [
    "auth_user_id",
    "stream_id",
    "turn_url",
    "turn_username",
    "turn_credential",
  ],
  data() {
    return {
      streamingPresenceChannel: null,
      broadcasterPeer: null,
      broadcasterId: null,
    };
  },

  methods: {
    joinBroadcast() {
      this.initializeStreamingChannel();
      this.initializeSignalOfferChannel(); // a private channel where the viewer listens to incoming signalling offer
    },

    initializeStreamingChannel() {
      this.streamingPresenceChannel = window.Echo.join(
        `streaming-channel.${this.stream_id}`
      );
    },

    createViewerPeer(incomingOffer, broadcaster) {
      const peer = new Peer({
        initiator: false,
        trickle: false,
        config: {
          iceServers: [
            {
              urls: "stun:stun.stunprotocol.org",
            },
            {
              urls: this.turn_url,
              username: this.turn_username,
              credential: this.turn_credential,
            },
          ],
        },
      });

      // Add Transceivers
      peer.addTransceiver("video", { direction: "recvonly" });
      peer.addTransceiver("audio", { direction: "recvonly" });

      // Initialize Peer events for connection to remote peer
      this.handlePeerEvents(
        peer,
        incomingOffer,
        broadcaster,
        this.removeBroadcastVideo
      );

      this.broadcasterPeer = peer;
    },

    handlePeerEvents(peer, incomingOffer, broadcaster, cleanupCallback) {
      peer.on("signal", (data) => {
        axios
          .post("/stream-answer", {
            broadcaster,
            answer: data,
          })
          .then((res) => {
            console.log(res);
          })
          .catch((err) => {
            console.log(err);
          });
      });

      peer.on("stream", (stream) => {
        // display remote stream
        this.$refs.viewer.srcObject = stream;
      });

      peer.on("track", (track, stream) => {
        console.log("onTrack");
      });

      peer.on("connect", () => {
        console.log("Viewer Peer connected");
      });

      peer.on("close", () => {
        console.log("Viewer Peer closed");
        peer.destroy();
        cleanupCallback();
      });

      peer.on("error", (err) => {
        console.log("handle error gracefully");
      });

      const updatedOffer = {
        ...incomingOffer,
        sdp: `${incomingOffer.sdp}\n`,
      };
      peer.signal(updatedOffer);
    },

    initializeSignalOfferChannel() {
      window.Echo.private(`stream-signal-channel.${this.auth_user_id}`).listen(
        "StreamOffer",
        ({ data }) => {
          console.log("Signal Offer from private channel");
          this.broadcasterId = data.broadcaster;
          this.createViewerPeer(data.offer, data.broadcaster);
        }
      );
    },

    removeBroadcastVideo() {
      console.log("removingBroadcast Video");
      alert("Livestream ended by broadcaster");
      const tracks = this.$refs.viewer.srcObject.getTracks();
      tracks.forEach((track) => {
        track.stop();
      });
      this.$refs.viewer.srcObject = null;
    },
  },
};
</script>

<style scoped>
</style>   